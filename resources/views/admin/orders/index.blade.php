@extends('layouts.admin')

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <h1>Orders</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Order
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="invoice_number" class="form-control" placeholder="Invoice Number" value="{{ request('invoice_number') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="customer_number" class="form-control" placeholder="Customer Number" value="{{ request('customer_number') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach(['Ordered', 'In process', 'In route', 'Delivered'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>{{ $order->invoice_number }}</td>
                    <td>{{ $order->customer->name }}</td>
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
                    <td>{{ $order->order_date->format('M d, Y') }}</td>
                    <td>{{ $order->creator->name }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No orders found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        {{ $orders->links() }}
    </div>
</div>
@endsection
