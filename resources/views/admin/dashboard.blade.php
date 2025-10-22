@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <h1>Dashboard</h1>
        <hr>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <h2>{{ $totalOrders }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Customers</h5>
                <h2>{{ $totalCustomers }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h2>{{ $totalUsers }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Orders by Status</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        @foreach(['Ordered', 'In process', 'In route', 'Delivered'] as $status)
                        <tr>
                            <td>{{ $status }}</td>
                            <td class="text-end">
                                <span class="badge bg-secondary">{{ $ordersByStatus[$status] ?? 0 }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Orders</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}">
                                    {{ $order->invoice_number }}
                                </a>
                            </td>
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
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No orders yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
