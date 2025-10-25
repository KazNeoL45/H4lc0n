@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Package Tracking</h1>

    <form method="POST" action="{{ route('tracking.search') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="customer_number" class="form-label">Customer Number</label>
                <input type="text" name="customer_number" id="customer_number" class="form-control"
                       placeholder="Ex. CUST-00123" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="invoice_number" class="form-label">Invoice Number</label>
                <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                       placeholder="Ex. FAC-00123" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    @if(session('error'))
        <div class="alert alert-danger mt-4">
            {{ session('error') }}
        </div>
    @endif

    @isset($order)
        <div class="card mt-5">
            <div class="card-body">
                <h5 class="card-title">Order Details</h5>
                <p><strong>Customer:</strong> {{ $customer->name }}</p>
                <p><strong>Customer Number:</strong> {{ $customer->customer_number }}</p>
                <p><strong>Invoice:</strong> {{ $order->invoice_number }}</p>
                <p><strong>Delivery Address:</strong> {{ $order->delivery_address }}</p>
                <p><strong>Order Date:</strong> {{ $order->order_date->format('Y-m-d H:i') }}</p>
                <p><strong>Status:</strong>
                    @switch($order->status)
                        @case('Ordered')
                            <span class="badge bg-secondary">Ordered</span>
                            @break
                        @case('In process')
                            <span class="badge bg-warning text-dark">In process</span>
                            @break
                        @case('In route')
                            <span class="badge bg-info text-dark">In route</span>
                            @break
                        @case('Delivered')
                            <span class="badge bg-success">Delivered</span>
                            @break
                    @endswitch
                </p>

                @if($order->products->count() > 0)
                <h5 class="mt-4">Products in this Order</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->products as $product)
                        <tr>
                            <td>{{ $product->title }}</td>
                            <td>{{ $product->pivot->quantity }}</td>
                            <td>${{ number_format($product->pivot->unit_price, 2) }}</td>
                            <td>${{ number_format($product->pivot->quantity * $product->pivot->unit_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">Order Total:</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @endif

                @if($order->photos->count() > 0)
                    <div class="mt-4">
                        <h5>Photo Evidence</h5>
                        <div class="row">
                            @php
                                $loadedPhoto = $order->photos->firstWhere('photo_type', 'loaded');
                                $deliveredPhoto = $order->photos->firstWhere('photo_type', 'delivered');
                            @endphp

                            @if($loadedPhoto && in_array($order->status, ['In route', 'Delivered']))
                                <div class="col-md-6 mb-3">
                                    <h6>Loaded Photo (In route):</h6>
                                    <img src="{{ asset('storage/' . $loadedPhoto->photo_path) }}"
                                         alt="Loaded photo" class="img-fluid rounded shadow">
                                </div>
                            @endif

                            @if($deliveredPhoto && $order->status === 'Delivered')
                                <div class="col-md-6 mb-3">
                                    <h6>Delivery Evidence:</h6>
                                    <img src="{{ asset('storage/' . $deliveredPhoto->photo_path) }}"
                                         alt="Delivery photo" class="img-fluid rounded shadow">
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    @if($order->status === 'Delivered')
                        <p class="text-muted mt-3">No photo evidence available.</p>
                    @endif
                @endif

            </div>
        </div>
    @endisset
</div>
@endsection


