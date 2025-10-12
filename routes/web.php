<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Auth;
use App\Exports\ProductsExport;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/user');
});

// Route::get('/test-email', function () {
//     return view('emails.forgot_password_otp', ['otp' => '1234']);
// });

Auth::routes();

Route::group(['middleware' => 'auth'], function(){

    Route::group(['prefix' => 'vendor', 'as'=>'vendor.'], function(){

        Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

        //category Requests
        Route::get('/category_request', [HomeController::class, 'categoryRequest'])->name('category_request');

        //products
        Route::get('/product', [ProductController::class, 'index'])->name('product.index');
        Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
        Route::post('/product', [ProductController::class, 'store'])->name('product.store');
        Route::post('/{id}/addImage', [ProductController::class, 'addImage'])->name('product.addImage');
        Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
        Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
        Route::get('/product/delete/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
        //export
        Route::get('/export-products', [ProductsExport::class, 'exportExcel'])->name('export.products');

        //profile
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::get('/profile/first_update', [ProfileController::class, 'create'])->name('profile.create');

        //Order
        Route::get('/orders/pending', [OrderController::class, 'pending'])->name('orders.pending');
        Route::get('/orders/accepted', [OrderController::class, 'accepted'])->name('orders.accepted');
        Route::get('/orders/supplied', [OrderController::class, 'supplied'])->name('orders.supplied');
        //export orders
        Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');
    });


});
