@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Product Details</h1>
        <div>
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->title }}" class="img-fluid rounded">
                    @else
                        <div class="alert alert-secondary">No image available</div>
                    @endif
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Title</th>
                            <td>{{ $product->title }}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $product->description ?? 'No description' }}</td>
                        </tr>
                        <tr>
                            <th>Price per Unit</th>
                            <td>${{ number_format($product->price_per_unit, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $product->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Updated At</th>
                            <td>{{ $product->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
