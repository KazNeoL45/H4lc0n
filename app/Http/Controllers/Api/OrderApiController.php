<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class OrderApiController extends Controller
{
    public function clientsCount(): JsonResponse
    {
        $count = Customer::count();

        return response()->json([
            'clients_count' => $count
        ]);
    }

    public function orderStatus(int $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'error' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'order_id' => $order->id,
            'invoice_number' => $order->invoice_number,
            'status' => $order->status
        ]);
    }

    public function deliveryImage(int $id): JsonResponse
    {
        $order = Order::with('photos')->find($id);

        if (!$order) {
            return response()->json([
                'error' => 'Order not found'
            ], 404);
        }

        if ($order->status !== 'Delivered') {
            return response()->json([
                'error' => 'Order status is not delivered'
            ], 400);
        }

        $deliveryPhoto = $order->photos()
            ->where('photo_type', 'delivered')
            ->first();

        if (!$deliveryPhoto) {
            return response()->json([
                'error' => 'Delivery image not found'
            ], 404);
        }

        $imageUrl = Storage::url($deliveryPhoto->photo_path);

        return response()->json([
            'order_id' => $order->id,
            'invoice_number' => $order->invoice_number,
            'status' => $order->status,
            'delivery_image' => $imageUrl,
            'uploaded_at' => $deliveryPhoto->created_at
        ]);
    }

    public function orderDetails(int $id): JsonResponse
    {
        $order = Order::with([
            'customer',
            'creator',
            'photos'
        ])->find($id);

        if (!$order) {
            return response()->json([
                'error' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'id' => $order->id,
            'invoice_number' => $order->invoice_number,
            'status' => $order->status,
            'order_date' => $order->order_date,
            'delivery_address' => $order->delivery_address,
            'notes' => $order->notes,
            'customer' => [
                'id' => $order->customer->id,
                'customer_number' => $order->customer->customer_number,
                'name' => $order->customer->name,
                'company_name' => $order->customer->company_name,
                'phone' => $order->customer->phone,
                'email' => $order->customer->email
            ],
            'creator' => [
                'id' => $order->creator->id,
                'name' => $order->creator->name,
                'email' => $order->creator->email,
                'role' => $order->creator->role
            ],
            'photos' => $order->photos->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'photo_type' => $photo->photo_type,
                    'photo_url' => Storage::url($photo->photo_path),
                    'uploaded_at' => $photo->created_at
                ];
            }),
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at
        ]);
    }
}
