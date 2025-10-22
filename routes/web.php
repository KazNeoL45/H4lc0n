<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false]);

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('orders', OrderController::class);
    Route::get('/orders-deleted', [OrderController::class, 'deleted'])->name('orders.deleted');
    Route::post('/orders/{id}/restore', [OrderController::class, 'restore'])->name('orders.restore');

    Route::resource('customers', CustomerController::class);

    Route::resource('users', UserController::class)->except(['show']);
});
