@extends('layouts.admin')

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <h1>Deleted Orders</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>{{ $order->invoice_number }}</td>
                    <td>{{ $order->customer->name }}</td>
                    <td>
                        <span class="badge bg-secondary">{{ $order->status }}</span>
                    </td>
                    <td>{{ $order->order_date->format('M d, Y') }}</td>
                    <td>
                        <form action="{{ route('admin.orders.restore', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Restore</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No deleted orders</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        {{ $orders->links() }}
    </div>
</div>
@endsection
