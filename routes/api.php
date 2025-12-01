<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderApiController;


Route::get('/clients/count', [OrderApiController::class, 'clientsCount']);

Route::get('/orders/statuses', [OrderApiController::class, 'orderStatuses']); 
Route::get('/orders/{id}/status', [OrderApiController::class, 'orderStatus']);
Route::get('/orders/{id}/delivery-image', [OrderApiController::class, 'deliveryImage']);
Route::get('/orders/{id}', [OrderApiController::class, 'orderDetails']);

// New route to get order by invoice number and customer id
Route::get('/orders/invoice/{invoice_number}/customer/{customer_id}', [OrderApiController::class, 'orderDetailsByInvoice']);
