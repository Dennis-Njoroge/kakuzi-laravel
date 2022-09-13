<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use JWTAuth;
use mysql_xdevapi\Exception;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $carts = $this->user->carts()
            ->with(['products'])
            ->join('products', 'products.id', '=', 'carts.product_id')
            ->get(['carts.*','products.name', 'products.image']);

        return response()->json($carts,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        //Validate data
        $data = $request->only('product_id', 'user_id', 'description', 'cart_quantity');
        $messages = [
            'product_id.unique' => 'Item already exists',
        ];
        $validator = Validator::make($data, [
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('carts')->where(function ($query) use($request) {
                    return $query->where('product_id', $request->product_id)
                        ->where('user_id', $this->user->id);
                }),
            ],
            'description'=> 'string',
            'cart_quantity' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 500);
        }

        //Request is valid, create new product
        $cart = $this->user->carts()->create([
            'product_id' => $request->product_id,
            'description' => $request->description,
            'cart_quantity' => $request->cart_quantity,
        ]);

        //Product created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'data' => $cart
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Cart $cart)
    {
        //Validate data
        $data = $request->only('product_id', 'user_id', 'description', 'cart_quantity');
        $validator = Validator::make($data, [
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('carts')->where(function ($query) use($request) {
                    return $query->where('product_id', $request->product_id)
                        ->where('user_id', $this->user->id);
                }),
            ],
            'description'=> 'string',
            'cart_quantity' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $cart = $this->user->carts()->create([
            'product_id' => $request->product_id,
            'description' => $request->description,
            'cart_quantity' => $request->cart_quantity,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'data' => $cart
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Cart $cart)
    {
            $cart->delete();
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart'
            ], Response::HTTP_OK);
    }

    public function clearCart ($id){
        Cart::where('user_id', $id)-> each(function ($cart, $key) {
            $cart->delete();
        });
        return response() -> json([
            'success'=>true,
            'message'=> 'Cart cleared successfully!',
        ], Response::HTTP_OK);
    }
}
