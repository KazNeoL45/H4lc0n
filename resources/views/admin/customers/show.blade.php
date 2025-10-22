@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Customer Details</h1>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-warning">Edit Customer</a>
        </div>
        <hr>

        <div class="card mb-3">
            <div class="card-header"><h5>Customer Information</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Customer Number:</th>
                        <td>{{ $customer->customer_number }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <th>Company Name:</th>
                        <td>{{ $customer->company_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tax ID:</th>
                        <td>{{ $customer->tax_id ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $customer->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $customer->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Fiscal Address:</th>
                        <td>{{ $customer->fiscal_address ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5>Orders ({{ $customer->orders->count() }})</h5></div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->orders as $order)
                        <tr>
                            <td>{{ $order->invoice_number }}</td>
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
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No orders yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Back to Customers</a>
    </div>
</div>
@endsection
