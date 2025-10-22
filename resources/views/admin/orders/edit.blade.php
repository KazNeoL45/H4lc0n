@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1>Edit Order: {{ $order->invoice_number }}</h1>
        <hr>

        <form action="{{ route('admin.orders.update', $order) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $order->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->customer_number }} - {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number', $order->invoice_number) }}" required>
                @error('invoice_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    @foreach(['Ordered', 'In process', 'In route', 'Delivered'] as $status)
                        <option value="{{ $status }}" {{ old('status', $order->status) === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Delivery Address</label>
                <textarea name="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" rows="3" required>{{ old('delivery_address', $order->delivery_address) }}</textarea>
                @error('delivery_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Order Date</label>
                <input type="datetime-local" name="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', $order->order_date->format('Y-m-d\TH:i')) }}" required>
                @error('order_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $order->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if(in_array(auth()->user()->role, ['Route', 'Admin']))
                <div class="mb-3">
                    <label class="form-label">Loaded Photo (for In route/Delivered status)</label>
                    <input type="file" name="loaded_photo" class="form-control @error('loaded_photo') is-invalid @enderror" accept="image/*">
                    @error('loaded_photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Delivered Photo (for Delivered status)</label>
                    <input type="file" name="delivered_photo" class="form-control @error('delivered_photo') is-invalid @enderror" accept="image/*">
                    @error('delivered_photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Update Order</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
