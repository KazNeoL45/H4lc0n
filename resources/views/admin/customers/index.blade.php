@extends('layouts.admin')

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <h1>Customers</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Customer
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Customer #</th>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->customer_number }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->company_name ?? '-' }}</td>
                    <td>{{ $customer->phone ?? '-' }}</td>
                    <td><span class="badge bg-secondary">{{ $customer->orders_count }}</span></td>
                    <td>
                        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No customers found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        {{ $customers->links() }}
    </div>
</div>
@endsection
