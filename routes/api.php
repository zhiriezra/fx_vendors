<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\VendorsController;
use App\Http\Controllers\Api\WalletController;

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
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);

Route::get('/states', [LocationController::class, 'statesList']);
Route::get('/state/{id}', [LocationController::class, 'state']);

Route::get('/lgas', [LocationController::class, 'lgasList']);
Route::get('/lga/{id}', [LocationController::class, 'lga']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthController::class, 'getUser']);

    // Profile Image and Signature upload
    Route::post('/upload-profile-image', [AuthController::class, 'uploadProfileImage']);
    Route::post('/upload-signature', [AuthController::class, 'uploadSignature']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('update-business', [VendorsController::class, 'updateBusiness']);

    // Product start
    Route::post('/add-product', [ProductController::class, 'store']);
    Route::post('/product/add-image', [ProductController::class, 'addImage']);
    Route::post('/product/delete-image', [ProductController::class, 'deleteImage']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/product/{id}', [ProductController::class, 'show']);
    Route::post('/product/update', [ProductController::class, 'update']);
    Route::delete('/product/{id}/delete', [ProductController::class, 'destroy']);
    // Product end

    // category request
    Route::get('/product-categories', [ProductController::class, 'categories']);
    Route::get('/product-category/{id}', [ProductController::class, 'category']);

    Route::post('/category/request', [ProductController::class, 'CatRequest'])->name('CatRequest');

    //Orders
    Route::get('/orders/{vendor_id}', [OrderController::class, 'index']);
    Route::get('/order/{order_id}/accept', [OrderController::class, 'accept']);
    Route::get('/order/{order_id}/decline', [OrderController::class, 'decline']);
    Route::get('/order/{order_id}/supplied', [OrderController::class, 'supplied']);
    Route::get('/orders/pending', [OrderController::class, 'pendingOrders']);
    Route::get('/orders/accepted', [OrderController::class, 'acceptedOrders']);
    Route::get('/orders/declined', [OrderController::class, 'declinedOrders']);
    Route::get('/orders/supplied', [OrderController::class, 'suppliedOrders']);


    // Wallet
    Route::get('/wallet-balance', [WalletController::class, 'getBalance']);
    Route::post('/withdrawal-request', [WalletController::class, 'requestWithdrawal']);
    Route::get('/withdrawal-requests', [WalletController::class, 'withdrawalRequests']);

    // Dashboard stats
    Route::get('/orders-supplied', [StatsController::class, 'supplied']);
    Route::get('/orders-pending', [StatsController::class, 'pending']);
    Route::get('/orders-accepted', [StatsController::class, 'accepted']);
    Route::get('/orders-total', [StatsController::class, 'total']);


});
