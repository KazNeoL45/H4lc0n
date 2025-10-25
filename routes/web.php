<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\Admin\ProductController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false]);
Route::get('/rastreo', function () {
    return view('rastreo');
})->name('rastreo');


Route::get('/rastreo', [TrackingController::class, 'index'])->name('rastreo');
Route::post('/rastreo/buscar', [TrackingController::class, 'search'])->name('rastreo.buscar');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class);
    Route::resource('orders', OrderController::class);
    Route::get('/orders-deleted', [OrderController::class, 'deleted'])->name('orders.deleted');
    Route::post('/orders/{id}/restore', [OrderController::class, 'restore'])->name('orders.restore');

    Route::resource('customers', CustomerController::class);

    Route::resource('users', UserController::class)->except(['show']);
});
