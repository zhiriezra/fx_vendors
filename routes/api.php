<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

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
Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Product start
    Route::get('/product', [ProductController::class, 'index'])->name('product.index');
    Route::post('/product', [ProductController::class, 'store'])->name('product.store');
    Route::post('/{id}/addImage', [ProductController::class, 'addImage'])->name('product.addImage');
    Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
    Route::put('/product/{id}/edit', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/product/{id}/delete', [ProductController::class, 'destroy'])->name('product.destroy');
    // Product end
    // category request
    Route::post('/category/request', [ProductController::class, 'CatRequest'])->name('CatRequest');

    //Orders
    Route::get('/vendor/orders', [OrderController::class, 'index']);
    Route::post('/vendor/orders/{order}/accept', [OrderController::class, 'accept']);
    Route::post('/vendor/orders/{order}/decline', [OrderController::class, 'decline']);
});
