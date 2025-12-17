<?php

use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Author\AuthController as AuthorAuthController;
use App\Http\Controllers\Author\BookController;
use App\Http\Controllers\Author\CategoryController as AuthorCategoryController;
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

Route::prefix('admin')->middleware(['auth:sanctum',AdminMiddleware::class])->group(function(){
    Route::apiResource('category',CategoryController::class);
    Route::apiResource('author',AuthorController::class);
    Route::put('author/{author}/approve',[AuthorController::class,'approve']);
});


Route::post('customer/sign-up',[CustomerAuthController::class,'signup']);
Route::prefix('customer')->middleware(['auth:sanctum',CustomerMiddleware::class])->group(function(){
    Route::apiResource('book',CustomerBookController::class)->only(['index','show']);
    Route::apiResource('category',CustomerCategoryController::class)->only('index');
    Route::apiResource('cart',CartController::class)->except('store');
    Route::post('cart/{book}',[CartController::class,'store']);
});

Route::post('author/sign-up',[AuthorAuthController::class,'signup']);
Route::prefix('author')->middleware(['auth:sanctum',AuthorMiddleware::class])->group(function(){
    Route::apiResource('book',BookController::class);
    Route::apiResource('category',AuthorCategoryController::class)->only('index');
});






