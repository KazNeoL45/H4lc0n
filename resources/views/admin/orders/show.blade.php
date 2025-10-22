@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Order Details</h1>
            <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-warning">Edit Order</a>
        </div>
        <hr>

        <div class="card mb-3">
            <div class="card-header"><h5>Order Information</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Invoice Number:</th>
                        <td>{{ $order->invoice_number }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge
                                @if($order->status === 'Delivered') bg-success
                                @elseif($order->status === 'In route') bg-info
                                @elseif($order->status === 'In process') bg-warning
                                @else bg-secondary
                                @endif">
                                {{ $order->status }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Order Date:</th>
                        <td>{{ $order->order_date->format('F d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Created By:</th>
                        <td>{{ $order->creator->name }} ({{ $order->creator->role }})</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5>Customer Information</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Customer Number:</th>
                        <td>{{ $order->customer->customer_number }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $order->customer->name }}</td>
                    </tr>
                    @if($order->customer->company_name)
                    <tr>
                        <th>Company:</th>
                        <td>{{ $order->customer->company_name }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5>Delivery Information</h5></div>
            <div class="card-body">
                <p><strong>Delivery Address:</strong></p>
                <p>{{ $order->delivery_address }}</p>
                @if($order->notes)
                <p><strong>Notes:</strong></p>
                <p>{{ $order->notes }}</p>
                @endif
            </div>
        </div>

        @if($order->photos->count() > 0)
        <div class="card mb-3">
            <div class="card-header"><h5>Photos</h5></div>
            <div class="card-body">
                <div class="row">
                    @foreach($order->photos as $photo)
                    <div class="col-md-6 mb-3">
                        <h6>{{ ucfirst($photo->photo_type) }} Photo</h6>
                        <img src="{{ asset('storage/' . $photo->photo_path) }}" class="img-fluid border" alt="{{ $photo->photo_type }}">
                        <small class="text-muted d-block mt-2">Uploaded by: {{ $photo->uploader->name }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
    </div>
</div>
@endsection
