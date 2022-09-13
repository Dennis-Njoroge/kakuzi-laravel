<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ProductController;

Route::post('login', [ApiController::class, 'authenticate']);
Route::post('register', [ApiController::class, 'register']);
Route::post('/users', [UserController::class, 'store']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('logout', [ApiController::class, 'logout']);
    Route::get('get-user', [ApiController::class, 'get_user']);
    Route::get('/users', [UserController::class,'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::resource('/categories', CategoryController::class);
    Route::resource('/products', ProductController::class);
    Route::resource('/carts', CartController::class);
    Route::delete('/carts/{id}/clear',[CartController::class,'clearCart']);
    Route::resource('/orders', OrderController::class);
    Route::post('/orders/{id}/approve', [OrderController::class, 'approveOrder']);
    Route::post('/orders/{id}/dispatch', [OrderController::class, 'dispatchOrder']);
    Route::post('/orders/{id}/delivered', [OrderController::class, 'deliverOrder']);
    Route::post('/orders/{id}/complete', [OrderController::class, 'confirmOrder']);
});
