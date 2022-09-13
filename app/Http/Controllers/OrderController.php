<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
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
        $orders = $this->user->orders()->with('orderItems')->get();

        return response()->json($orders);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allOrders($status)
    {
        $orders = Order::where('status',);
        if (strtoupper($status) == 'ALL'){

        }
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
//
        $data = $request->only( 'transaction_code', 'shipment_fee', 'total_amount', 'paid_amount', 'delivery_address', 'items');

        $validator = Validator::make($data, [
            'transaction_code'=>'required|unique:orders',
            'total_amount'=>'numeric|required',
            'paid_amount'=>'numeric|required',
            'delivery_address' => 'string|required',
            "items"    => "required|array|min:1",
            'items.*.product_id'=>'required|exists:products,id',
            'items.*.quantity'=>'required',
            'items.*.price'=>'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 500);
        }

        $model = new Order();

        //Request is valid, create new product
        $order = $this->user->orders()->create([
            'order_number' => $model->getOrderNumber(),
            'transaction_code' => $request->transaction_code,
            'shipment_fee' => $request->shipment_fee,
            'total_amount' => $request->total_amount,
            'paid_amount' => $request->paid_amount,
            'delivery_address'=> $request->delivery_address
        ]);

        if ($order){
            $data = $request->items;
            $items = [];
            if (count($data) > 0){
                foreach ($data as $datum){
                    $items [] = [
                        'order_id'=> $order->id,
                        'product_id'=> $datum['product_id'],
                        'quantity'=> $datum['quantity'],
                        'price' => $datum['price'],
                        'discount'=> $datum['discount'] ?? 0
                    ];
                }
            }
            $orderItems = $order->orderItems()->insert($items);
        }

        //Product created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully',
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order)
    {
        return response()->json($order->with('orderItems')->first());
    }

    public function approveOrder ($id){
        $order = Order::where('id',$id);

        if (!$order->first()){
            return response()->json([
                'message' => 'Order not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($order->first()->status != 'Pending'){
            return response()->json([
                'status'=> false,
                'message' => 'Order is already approved!'
            ], Response::HTTP_OK);
        }

        $order->update([
            'status'=>'Approved',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order approved successfully'
        ], Response::HTTP_OK);
    }

    public function dispatchOrder (Request $request, $id){
        $order = Order::where('id',$id);

        if (!$order->first()){
            return response()->json([
                'message' => 'Order not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = $request->only( 'driver_id');

        $validator = Validator::make($data, [
            'driver_id'=>'required|exists:users,id',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $orderStatus = $order->first()->status;
        if ( $orderStatus === 'Pending'){
            return response()->json([
                'status'=> false,
                'message' => 'Order pending approval!'
            ], Response::HTTP_OK);
        }

        if ( $orderStatus === 'Dispatched'){
            return response()->json([
                'status'=> false,
                'message' => 'Order already dispatched!'
            ], Response::HTTP_OK);
        }

        $order->update([
            'status'=>'Dispatched',
            'driver_id'=> $request->driver_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order dispatched successfully!'
        ], Response::HTTP_OK);
    }

    public function deliverOrder ($id){
        $order = Order::where('id',$id);

        if (!$order->first()){
            return response()->json([
                'message' => 'Order not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($order->first()->status != 'Dispatched'){
            return response()->json([
                'status'=> false,
                'message' => 'Order is yet to be dispatched!'
            ], Response::HTTP_OK);
        }

        $order->update([
            'status'=>'Delivered',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order delivered successfully'
        ], Response::HTTP_OK);
    }

    public function confirmOrder ($id){
        $order = Order::where('id',$id);

        if (!$order->first()){
            return response()->json([
                'message' => 'Order not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($order->first()->status != 'Delivered'){
            return response()->json([
                'status'=> false,
                'message' => 'Order is yet to be delivered!'
            ], Response::HTTP_OK);
        }

        $order->update([
            'status'=>'Completed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order delivery confirmed successfully'
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Order $order)
    {
        if ($order->status === 'Pending'){
            $order->delete();
            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ], Response::HTTP_OK);
        }
        return response()->json([
            'success' => false,
            'message' => 'Order cannot be cancelled'
        ], Response::HTTP_OK);
    }
}
