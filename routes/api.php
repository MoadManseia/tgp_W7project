<?php

use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Author\AuthController as AuthorAuthController;
use App\Http\Controllers\Author\BookController;
use App\Http\Controllers\Author\CategoryController as AuthorCategoryController;
use App\Http\Controllers\Author\OrderController as AuthorOrderController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\BookController as CustomerBookController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CategoryController as CustomerCategoryController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthorMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/login',[AuthController::class,'login']);

Route::prefix('admin')->middleware([AdminMiddleware::class, 'auth:sanctum'])->group(function(){
    Route::put('order/{order}/status',[AdminOrderController::class,'updateStatus']);
    Route::apiResource('category',CategoryController::class);
    Route::apiResource('author',AuthorController::class);
    Route::put('author/{author}/approve',[AuthorController::class,'approve']);
    Route::apiResource('order',AdminOrderController::class)->only(['index','show']);
});


Route::post('customer/sign-up',[CustomerAuthController::class,'signup']);
Route::prefix('customer')->middleware(['auth:sanctum',CustomerMiddleware::class])->group(function(){
    Route::apiResource('book',CustomerBookController::class)->only(['index','show']);
    Route::apiResource('category',CustomerCategoryController::class)->only('index');
    Route::delete('cart/{book}',[CartController::class,'decreaseQty']);
    Route::post('cart/checkout',[CartController::class,'checkout']);
    Route::put('cart/address',[CartController::class,'updateAddress']);
    Route::get('orders',[CartController::class,'viewOrders']);
    Route::apiResource('cart',CartController::class)->except(['store','update', 'delete']);
    Route::post('cart/{book}',[CartController::class,'store']);
});

Route::post('author/sign-up',[AuthorAuthController::class,'signup']);
Route::prefix('author')->middleware(['auth:sanctum',AuthorMiddleware::class])->group(function(){
    Route::apiResource('book',BookController::class);
    Route::apiResource('category',AuthorCategoryController::class)->only('index');
    Route::get('orders',[AuthorOrderController::class,'index']);
});






