<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaystackController;
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

// Locations list
Route::get('/countries', [LocationController::class, 'countriesList']);
Route::get('/states', [LocationController::class, 'statesList']);
Route::get('/state/{id}', [LocationController::class, 'state']);

Route::get('/lgas', [LocationController::class, 'lgasList']);
Route::get('/lga/{id}', [LocationController::class, 'lga']);

Route::middleware('auth:sanctum')->group(function () {
    // user routes start
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/delete-account', [AuthController::class, 'delete']);
    Route::post('/save-notification-token', [NotificationController::class, 'storeToken']);
    // user routes end

    // routes to update profiles start
    Route::post('/update-bio', [AuthController::class, 'updateBio']);
    Route::post('/update-location', [AuthController::class, 'updateLocation']);
    Route::post('/update-buisness', [AuthController::class, 'updateBusiness']);
    Route::post('/update-password', [AuthController::class, 'changePassword']);
    Route::post('/upload-profile-image', [AuthController::class, 'uploadProfileImage']);
    Route::post('/upload-signature', [AuthController::class, 'uploadSignature']);
    // routes to update profiles end

    // Product start
    Route::post('/add-product', [ProductController::class, 'store']);
    Route::post('/product/add-image', [ProductController::class, 'addImage']);
    Route::post('/product/delete-image', [ProductController::class, 'deleteImage']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/product/{id}', [ProductController::class, 'show']);
    Route::post('/product/update', [ProductController::class, 'update']);
    Route::delete('/product/{id}/delete', [ProductController::class, 'destroy']);
    Route::get('/products-stats', [ProductController::class, 'productStats']);
    Route::get('/products-low-stock', [ProductController::class, 'lowStockProducts']);
    Route::get('/products-out-of-stock', [ProductController::class, 'outOfStockProducts']);
    Route::post('/product/restock', [ProductController::class, 'restockProduct']);
    // Product end

    //Export Products
    Route::get('products/export', [ProductController::class, 'export'])
    ->name('product.export');

    // category request
    Route::get('/product-categories', [ProductController::class, 'categories']);
    Route::get('/product-category/{id}', [ProductController::class, 'category']);

    Route::post('/category/request', [ProductController::class, 'CatRequest'])->name('CatRequest');

    //Orders
    Route::get('/order/{order_id}/accept', [OrderController::class, 'accept']);
    Route::get('/order/{order_id}/decline', [OrderController::class, 'decline']);
    Route::get('/order/{order_id}/supplied', [OrderController::class, 'supplied']);
    Route::get('/orders/pending', [OrderController::class, 'pendingOrders']);
    Route::get('/orders/accepted', [OrderController::class, 'acceptedOrders']);
    Route::get('/orders/declined', [OrderController::class, 'declinedOrders']);
    Route::get('/orders/supplied', [OrderController::class, 'suppliedOrders']);
    Route::get('/orders/{vendor_id}', [OrderController::class, 'index']);

    //Export User Orders
    Route::get('order/export', [OrderController::class, 'exportOrder'])
    ->name('order.export');

    // Wallet
    Route::get('/wallet-balance', [WalletController::class, 'getBalance']);
    Route::post('/request-payout', [WalletController::class, 'requestWithdrawal']);
    Route::get('/withdrawal-requests', [WalletController::class, 'withdrawalRequests']);

    Route::get('/recent-transactions', [WalletController::class, 'transactions']);

    //transactions Export
    Route::get('/export-transactions', [WalletController::class, 'exportTransactions']);

    // Dashboard stats
    Route::get('/dashboard-stat', [StatsController::class, 'dashboardStats']);


});
Route::get('/banks', [StatsController::class, 'getBankList']);
