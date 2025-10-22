<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        return view('admin.orders.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|unique:orders,invoice_number',
            'delivery_address' => 'required',
            'notes' => 'nullable',
            'order_date' => 'required|date',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'Ordered';

        Order::create($validated);

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order created successfully.');
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'creator', 'photos.uploader']);
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        if ($order->is_deleted) {
            return redirect()->route('admin.orders.deleted')
                ->with('error', 'Cannot edit deleted order.');
        }

        $customers = Customer::orderBy('name')->get();
        return view('admin.orders.edit', compact('order', 'customers'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|unique:orders,invoice_number,' . $order->id,
            'delivery_address' => 'required',
            'notes' => 'nullable',
            'status' => 'required|in:Ordered,In process,In route,Delivered',
            'order_date' => 'required|date',
        ]);

        $order->update($validated);

        if ($request->hasFile('loaded_photo') && in_array($order->status, ['In route', 'Delivered'])) {
            $path = $request->file('loaded_photo')->store('order_photos', 'public');

            OrderPhoto::updateOrCreate(
                ['order_id' => $order->id, 'photo_type' => 'loaded'],
                ['photo_path' => $path, 'uploaded_by' => auth()->id()]
            );
        }

        if ($request->hasFile('delivered_photo') && $order->status === 'Delivered') {
            $path = $request->file('delivered_photo')->store('order_photos', 'public');

            OrderPhoto::updateOrCreate(
                ['order_id' => $order->id, 'photo_type' => 'delivered'],
                ['photo_path' => $path, 'uploaded_by' => auth()->id()]
            );
        }

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order updated successfully.');
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
