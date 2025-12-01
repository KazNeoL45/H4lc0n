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

        // Eager load photos to include images in the response
        $query = Order::with(['customer', 'photos'])
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
                // prefer the delivered photo
                $delivered = $order->photos->firstWhere('photo_type', 'delivered');

                $deliveredData = $delivered ? $this->photoToBase64($delivered->photo_path) : ['base64' => null, 'mime' => null];

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
                    'delivered_photo' => [
                        'id' => $delivered ? $delivered->id : null,
                        'photo_type' => $delivered ? $delivered->photo_type : null,
                        'raw_photo_path' => $delivered ? $delivered->photo_path : null,
                        'photo_base64' => $deliveredData['base64'],
                        'photo_mime' => $deliveredData['mime'],
                        'uploaded_at' => $delivered ? $delivered->created_at : null,
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
     * Convert a stored route/path/URL to base64 + mime. Tries common disks and filesystem locations.
     */
    private function photoToBase64(?string $photoPath): array
    {
        $photoPath = trim((string) $photoPath);
        if ($photoPath === '') {
            return ['base64' => null, 'mime' => null];
        }

        // If it's a full URL, try fetching it
        if (preg_match('/^https?:\\/\\//i', $photoPath)) {
            $contents = @file_get_contents($photoPath);
            if ($contents !== false) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_buffer($finfo, $contents) ?: 'application/octet-stream';
                finfo_close($finfo);
                return ['base64' => 'data:' . $mime . ';base64,' . base64_encode($contents), 'mime' => $mime];
            }
            return ['base64' => null, 'mime' => null];
        }

        // normalize path (remove leading slash)
        $path = ltrim($photoPath, '/');

        // Try Storage disks (public then default)
        $disks = array_filter([ 'public', config('filesystems.default') ]);
        foreach (array_unique($disks) as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    $contents = Storage::disk($disk)->get($path);
                    $mime = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
                    return ['base64' => 'data:' . $mime . ';base64,' . base64_encode($contents), 'mime' => $mime];
                }
            } catch (\Throwable $e) {
                // ignore and continue to next option
            }
        }

        // Try common local filesystem locations using the stored route
        $candidates = [
            public_path($path),                    // e.g. public/orders/...
            public_path('storage/' . $path),       // public/storage/...
            storage_path('app/' . $path),          // storage/app/...
            storage_path('app/public/' . $path),   // storage/app/public/...
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate) && is_readable($candidate)) {
                $contents = @file_get_contents($candidate);
                if ($contents !== false) {
                    $mime = @mime_content_type($candidate) ?: 'application/octet-stream';
                    return ['base64' => 'data:' . $mime . ';base64,' . base64_encode($contents), 'mime' => $mime];
                }
            }
        }

        // Nothing found
        return ['base64' => null, 'mime' => null];
    }
}
