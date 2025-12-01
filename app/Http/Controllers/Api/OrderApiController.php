<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderApiController extends Controller
{
    /**
     * Get total count of clients
     */
    public function clientsCount(): JsonResponse
    {
        $count = Customer::count();

        return response()->json([
            'success' => true,
            'clients_count' => $count
        ]);
    }

    /**
     * Get status of a specific order by ID
     *
     * @param int $id Order ID
     */
    public function orderStatus(int $id): JsonResponse
    {
        // Validate ID is a positive integer
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid order ID. ID must be a positive integer.'
            ], 400);
        }

        $order = Order::where('is_deleted', false)->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found or has been deleted.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'invoice_number' => $order->invoice_number,
                'status' => $order->status,
                'order_date' => $order->order_date,
                'updated_at' => $order->updated_at
            ]
        ]);
    }

    /**
     * Get delivery image for a delivered order
     *
     * @param int $id Order ID
     */
    public function deliveryImage(int $id): JsonResponse
    {
        // Validate ID is a positive integer
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid order ID. ID must be a positive integer.'
            ], 400);
        }

        $order = Order::with('photos')->where('is_deleted', false)->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found or has been deleted.'
            ], 404);
        }

        if ($order->status !== 'Delivered') {
            return response()->json([
                'success' => false,
                'error' => 'Order status is not delivered. Current status: ' . $order->status
            ], 400);
        }

        $deliveryPhoto = $order->photos()
            ->where('photo_type', 'delivered')
            ->first();

        if (!$deliveryPhoto) {
            return response()->json([
                'error' => 'Delivery image not found for this order.'
            ], 404);
        }

        // Convert to base64
        $photoPath = $deliveryPhoto->photo_path;
        $base64 = null;
        $mime = null;
        if (Storage::exists($photoPath)) {
            $contents = Storage::get($photoPath);
            $mime = Storage::mimeType($photoPath) ?? 'application/octet-stream';
            $base64 = 'data:' . $mime . ';base64,' . base64_encode($contents);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'photo_id' => $deliveryPhoto->id,
                'photo_type' => $deliveryPhoto->photo_type,
                'photo_base64' => $base64,
                'photo_mime' => $mime,
                'uploaded_at' => $deliveryPhoto->created_at,
            ]
        ]);
    }

    /**
     * Get complete details of an order
     *
     * @param int $id Order ID
     */
    public function orderDetails(int $id): JsonResponse
    {
        // Validate ID is a positive integer
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid order ID. ID must be a positive integer.'
            ], 400);
        }

        $order = Order::with([
            'customer',
            'creator',
            'photos',
            'products'
        ])->where('is_deleted', false)->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found or has been deleted.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'invoice_number' => $order->invoice_number,
                'status' => $order->status,
                'order_date' => $order->order_date,
                'delivery_address' => $order->delivery_address,
                'total_amount' => $order->total_amount,
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
                'products' => $order->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'title' => $product->title,
                        'quantity' => $product->pivot->quantity,
                        'unit_price' => $product->pivot->unit_price,
                        'subtotal' => $product->pivot->quantity * $product->pivot->unit_price
                    ];
                }),
                'photos' => $order->photos->map(function ($photo) {
                    $photoPath = $photo->photo_path;
                    $base64 = null;
                    $mime = null;
                    if (Storage::exists($photoPath)) {
                        $contents = Storage::get($photoPath);
                        $mime = Storage::mimeType($photoPath) ?? 'application/octet-stream';
                        $base64 = 'data:' . $mime . ';base64,' . base64_encode($contents);
                    }
                    return [
                        'id' => $photo->id,
                        'photo_type' => $photo->photo_type,
                        'photo_base64' => $base64,
                        'photo_mime' => $mime,
                        'uploaded_at' => $photo->created_at
                    ];
                }),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at
            ]
        ]);
    }

    /**
     * Get order details by invoice number and customer id
     *
     * @param string $invoice_number
     * @param int $customer_id
     */
    public function orderDetailsByInvoice(string $invoice_number, int $customer_id): JsonResponse
    {
        // Validate inputs
        $validator = Validator::make([
            'invoice_number' => $invoice_number,
            'customer_id' => $customer_id
        ], [
            'invoice_number' => 'required|string|max:255',
            'customer_id' => 'required|integer|exists:customers,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::with([
            'customer',
            'creator',
            'photos',
            'products'
        ])->where('is_deleted', false)
          ->where('invoice_number', $invoice_number)
          ->where('customer_id', $customer_id)
          ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found for given invoice number and customer.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'invoice_number' => $order->invoice_number,
                'status' => $order->status,
                'order_date' => $order->order_date,
                'delivery_address' => $order->delivery_address,
                'total_amount' => $order->total_amount,
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
                'products' => $order->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'title' => $product->title,
                        'quantity' => $product->pivot->quantity,
                        'unit_price' => $product->pivot->unit_price,
                        'subtotal' => $product->pivot->quantity * $product->pivot->unit_price
                    ];
                }),
                'photos' => $order->photos->map(function ($photo) {
                    $photoPath = $photo->photo_path;
                    $base64 = null;
                    $mime = null;
                    if (Storage::exists($photoPath)) {
                        $contents = Storage::get($photoPath);
                        $mime = Storage::mimeType($photoPath) ?? 'application/octet-stream';
                        $base64 = 'data:' . $mime . ';base64,' . base64_encode($contents);
                    }
                    return [
                        'id' => $photo->id,
                        'photo_type' => $photo->photo_type,
                        'photo_base64' => $base64,
                        'photo_mime' => $mime,
                        'uploaded_at' => $photo->created_at
                    ];
                }),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at
            ]
        ]);
    }

    /**
     * Get list of orders with optional filters
     */
    public function orderStatuses(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['nullable', Rule::in(['Ordered', 'In process', 'In route', 'Delivered'])],
            'customer_id' => 'nullable|integer|exists:customers,id',
            'invoice_number' => 'nullable|string|max:255',
            'date_from' => 'nullable|date|before_or_equal:today',
            'date_to' => 'nullable|date|after_or_equal:date_from|before_or_equal:today',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Order::with(['customer'])
            ->where('is_deleted', false);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }


        $perPage = $request->input('per_page', 15);
        $perPage = min($perPage, 100); 

        $orders = $query->latest('order_date')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'invoice_number' => $order->invoice_number,
                    'status' => $order->status,
                    'order_date' => $order->order_date,
                    'total_amount' => $order->total_amount,
                    'customer' => [
                        'id' => $order->customer->id,
                        'customer_number' => $order->customer->customer_number,
                        'name' => $order->customer->name,
                        'company_name' => $order->customer->company_name
                    ],
                    'updated_at' => $order->updated_at
                ];
            }),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem()
            ]
        ]);
    }

    /**
     * Try multiple locations/disks and return base64 + mime (or nulls)
     */
    private function photoToBase64(?string $photoPath): array
    {
        $base64 = null;
        $mime = null;

        if (empty($photoPath)) {
            return ['base64' => null, 'mime' => null];
        }

        // 1) Try default Storage
        if (Storage::exists($photoPath)) {
            $contents = Storage::get($photoPath);
            $mime = Storage::mimeType($photoPath) ?: 'application/octet-stream';
        } else {
            // 2) Try storage/app/<path>
            $local = storage_path('app/' . ltrim($photoPath, '/'));
            if (file_exists($local)) {
                $contents = file_get_contents($local);
                $mime = mime_content_type($local) ?: 'application/octet-stream';
            } else {
                // 3) Try public path (e.g. public/storage/...)
                $public = public_path(ltrim($photoPath, '/'));
                if (file_exists($public)) {
                    $contents = file_get_contents($public);
                    $mime = mime_content_type($public) ?: 'application/octet-stream';
                } else {
                    // 4) Try the public disk
                    if (Storage::disk('public')->exists($photoPath)) {
                        $contents = Storage::disk('public')->get($photoPath);
                        $mime = Storage::disk('public')->mimeType($photoPath) ?: 'application/octet-stream';
                    }
                }
            }
        }

        if (isset($contents) && $contents !== false) {
            $base64 = 'data:' . $mime . ';base64,' . base64_encode($contents);
        }

        return ['base64' => $base64, 'mime' => $mime];
    }
}
