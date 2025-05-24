<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\TransactionsController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VendorsController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\OrderAcceptController;
use App\Http\Controllers\Api\PaystackController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\OrderDeclineController;
use App\Http\Controllers\Api\OrderSupplyController;

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

Route::prefix('v1')->group(function() {
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);

    // Password reset routes
    Route::post('/password/reset/send-otp', [AuthController::class, 'sendForgotPasswordOTP']);
    Route::post('/password/reset/verify-otp', [AuthController::class, 'verifyForgotPasswordOTP']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);

    // Locations list
    Route::get('/countries', [LocationController::class, 'countriesList']);
    Route::get('/states', [LocationController::class, 'statesList']);
    Route::get('/state/{id}', [LocationController::class, 'state']);
    Route::get('/lgas', [LocationController::class, 'lgasList']);
    Route::get('/lga/{id}', [LocationController::class, 'lga']);
    Route::get('/banks', [LocationController::class, 'getBankList']);
    Route::get('/units', [LocationController::class, 'unitList']);

});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // user routes start
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/delete-account', [AuthController::class, 'delete']);
    Route::post('/save-notification-token', [NotificationController::class, 'storeToken']);
    // user routes end

    // routes to update profiles start
    Route::post('/update-bio', [AuthController::class, 'updateBio']);
    Route::post('/update-location', [AuthController::class, 'updateLocation']);
    Route::post('/update-business', [AuthController::class, 'updateBusiness']);
    Route::post('/patch-business', [AuthController::class, 'updateBusinessPartial']);
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
    Route::get('/products/inventory-breakdown', [ProductController::class, 'inventoryBreakdown']);
    // Product end

    //Export Products
    Route::get('products/export', [ProductController::class, 'export'])
    ->name('product.export');

    // category request
    Route::get('/product-categories', [ProductController::class, 'categories']);
    Route::get('/product-category/{id}', [ProductController::class, 'category']);

    Route::post('/category/request', [ProductController::class, 'CatRequest'])->name('CatRequest');

    //Orders
    Route::get('/order/{order_id}', [OrderController::class, 'singleOrder']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/order/{id}/update-status', [OrderController::class, 'updateOrderStatus']);

    Route::get('/sales-record', [OrderController::class, 'salesRecord']);
    Route::get('/sales-detail/{id}', [OrderController::class, 'salesDetail']);


    //Export User Orders
    Route::get('orders/export', [OrderController::class, 'exportOrders']);

    // Wallet

    //Route::post('/request-payout', [WalletController::class, 'requestWithdrawal']);
    //Route::get('/withdrawal-requests', [WalletController::class, 'withdrawalRequests']);


    Route::get('/wallet-balance', [WalletController::class, 'getBalance']);
    Route::get('/wallet-enquiry', [WalletController::class, 'walletEnquiry']);
    Route::get('/recent-transactions', [WalletController::class, 'transactions']);
    Route::post('/withdrawal-requests', [WalletController::class, 'fundWithdraw']);


    //transactions Export
    Route::get('/transactions/export', [TransactionsController::class, 'exportTransactions'])
    ->name('transactions.export');

    // Dashboard stats
    Route::get('/dashboard-stat', [StatsController::class, 'dashboardStats']);


    // Notification routes
    Route::post('/notifications/token', [NotificationController::class, 'storeToken']);
    Route::post('/notifications/send', [NotificationController::class, 'sendNotification']);
    Route::post('/notifications/test', [NotificationController::class, 'testNotification']);

    // Push Notification Routes
    Route::prefix('notifications')->group(function () {
        Route::post('/send', [NotificationController::class, 'sendNotification']);
        Route::post('/test', [NotificationController::class, 'testNotification']);
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::get('/statistics', [NotificationController::class, 'statistics']);
    });



});
