<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'creator'])
            ->where('is_deleted', false);

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('customer_number')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('customer_number', 'like', '%' . $request->customer_number . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('order_date', $request->date);
        }

        $orders = $query->latest('order_date')->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('title')->get();
        
        return view('admin.orders.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|string|unique:orders,invoice_number',
            'delivery_address' => 'required|string',
            'order_date' => 'required|date',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $totalAmount = 0;
        $productsToSync = [];

        try {
            DB::beginTransaction();

            $productIds = array_column($validatedData['products'], 'product_id');
            $productsFromDB = Product::findMany($productIds)->keyBy('id');

            foreach ($validatedData['products'] as $item) {
                $product = $productsFromDB->get($item['product_id']);
                
                if (!$product) {
                    throw new \Exception("Producto con ID {$item['product_id']} no encontrado.");
                }
                
                $unitPrice = $product->price_per_unit;
                $quantity = $item['quantity'];
                $subtotal = $unitPrice * $quantity;
                $totalAmount += $subtotal;

                $productsToSync[$product->id] = [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ];
            }

            $order = Order::create([
                'customer_id' => $validatedData['customer_id'],
                'invoice_number' => $validatedData['invoice_number'],
                'delivery_address' => $validatedData['delivery_address'],
                'order_date' => $validatedData['order_date'],
                'notes' => $validatedData['notes'],
                'total_amount' => $totalAmount,
                'created_by' => Auth::id(),
                'status' => 'Ordered',
            ]);

            $order->products()->sync($productsToSync);

            DB::commit();

            return redirect()->route('admin.orders.index')->with('success', 'Orden creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al crear la orden: ' . $e->getMessage());
        }
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'creator', 'photos.uploader', 'products']);
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        if ($order->is_deleted) {
            return redirect()->route('admin.orders.deleted')
                ->with('error', 'Cannot edit deleted order.');
        }

        $customers = Customer::orderBy('name')->get();
        $hasDeliveredPhoto = $order->photos()->where('photo_type', 'delivered')->exists();

        return view('admin.orders.edit', compact('order', 'customers', 'hasDeliveredPhoto'));
    }

    public function update(Request $request, Order $order)
    {
        $hasDeliveredPhoto = $order->photos()->where('photo_type', 'delivered')->exists();

        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => [
                'required',
                'string',
                Rule::unique('orders')->ignore($order->id),
            ],
            'status' => 'required|string',
            'delivery_address' => 'required|string',
            'order_date' => 'required|date',
            'notes' => 'nullable|string',
            'loaded_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'delivered_photo' => [
                'nullable',
                Rule::requiredIf($request->status === 'Delivered' && !$hasDeliveredPhoto),
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048'
            ],
        ]);

        try {
            DB::beginTransaction();

            if ($request->hasFile('loaded_photo')) {
                $oldLoaded = $order->photos()->where('photo_type', 'loaded')->first();
                if ($oldLoaded) {
                    Storage::disk('public')->delete($oldLoaded->photo_path);
                    $oldLoaded->delete();
                }

                $path = $request->file('loaded_photo')->store('order_photos', 'public');
                $order->photos()->create([
                    'photo_path' => $path,
                    'photo_type' => 'loaded',
                    'uploaded_by' => Auth::id(),
                ]);
            }

            if ($request->hasFile('delivered_photo')) {
                $oldDelivered = $order->photos()->where('photo_type', 'delivered')->first();
                if ($oldDelivered) {
                    Storage::disk('public')->delete($oldDelivered->photo_path);
                    $oldDelivered->delete();
                }

                $path = $request->file('delivered_photo')->store('order_photos', 'public');
                $order->photos()->create([
                    'photo_path' => $path,
                    'photo_type' => 'delivered',
                    'uploaded_by' => Auth::id(),
                ]);

                // Force order status to Delivered when a delivered photo is uploaded
                $validatedData['status'] = 'Delivered';
            }

            $orderData = $validatedData;
            unset($orderData['loaded_photo'], $orderData['delivered_photo']);
            $order->update($orderData);

            DB::commit();

            return redirect()->route('admin.orders.index')->with('success', 'Orden actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar la orden: ' . $e->getMessage());
        }
    }

    public function destroy(Order $order)
    {
        $order->update(['is_deleted' => true]);

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    public function deleted()
    {
        $orders = Order::with(['customer', 'creator'])
            ->where('is_deleted', true)
            ->latest('order_date')
            ->paginate(15);

        return view('admin.orders.deleted', compact('orders'));
    }

    public function restore($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['is_deleted' => false]);

        return redirect()->route('admin.orders.deleted')
            ->with('success', 'Order restored successfully.');
    }
    
}

