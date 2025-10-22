<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderApiController;

Route::get('/clients/count', [OrderApiController::class, 'clientsCount']);
Route::get('/orders/{id}/status', [OrderApiController::class, 'orderStatus']);
Route::get('/orders/{id}/delivery-image', [OrderApiController::class, 'deliveryImage']);
Route::get('/orders/{id}', [OrderApiController::class, 'orderDetails']);
