<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VendorsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-email', [AuthController::class, 'loginEmail']);

Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('update-business', [VendorsController::class, 'updateBusiness']);

    // Product start
    Route::post('/add-product', [ProductController::class, 'store']);
    Route::post('/product/add-image', [ProductController::class, 'addImage']);
    Route::post('/product/delete-image', [ProductController::class, 'deleteImage']);
    Route::get('/product/{id}', [ProductController::class, 'show']);
    Route::post('/product/update', [ProductController::class, 'update']);
    Route::delete('/product/{id}/delete', [ProductController::class, 'destroy']);
    // Product end

    // category request
    Route::post('/category/request', [ProductController::class, 'CatRequest'])->name('CatRequest');

    //Orders
    Route::get('/orders/{vendor_id}', [OrderController::class, 'index']);
    Route::get('/order/{order_id}/accept', [OrderController::class, 'accept']);
    Route::get('/order/{order_id}/decline', [OrderController::class, 'decline']);
    Route::get('/order/{order_id}/supplied', [OrderController::class, 'supplied']);

});
