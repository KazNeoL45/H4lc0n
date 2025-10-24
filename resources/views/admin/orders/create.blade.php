@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1"> <h1>Create New Order</h1>
        <hr>

        <form action="{{ route('admin.orders.store') }}" method="POST" 
              x-data="{ 
                  products: {{ $products->toJson() }}, 
                  lineItems: [ { product_id: '', quantity: 1 } ],
                  get total() {
                      let calcTotal = 0;
                      this.lineItems.forEach(item => {
                          let product = this.products.find(p => p.id == item.product_id);
                          let price = product ? product.price_per_unit : 0;
                          calcTotal += (price * (item.quantity || 0));
                      });
                      return calcTotal.toFixed(2);
                  }
              }">
            @csrf

            {{-- --- SECCIÓN DE DATOS DE LA ORDEN --- --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->customer_number }} - {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number') }}" required>
                        @error('invoice_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Delivery Address</label>
                <textarea name="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" rows="3" required>{{ old('delivery_address') }}</textarea>
                @error('delivery_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Order Date</label>
                        <input type="datetime-local" name="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', now()->format('Y-m-d\TH:i')) }}" required>
                        @error('order_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- --- SECCIÓN DE PRODUCTOS (LÍNEAS DE ORDEN) --- --}}
            <hr>
            <h3>Products</h3>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in lineItems" :key="index">
                        <tr>
                            <td>
                                <select :name="'products[' + index + '][product_id]'" class="form-select" x-model="item.product_id" required>
                                    <option value="">Select product</option>
                                    <template x-for="product in products">
                                        <option :value="product.id" x-text="product.title"></option>
                                    </template>
                                </select>
                            </td>
                            <td>
                                <input type="number" :name="'products[' + index + '][quantity]'" class="form-control" x-model.number="item.quantity" min="1" required>
                            </td>
                            <td>
                                <span x-text="products.find(p => p.id == item.product_id) ? '$' + products.find(p => p.id == item.product_id).price_per_unit : '$0.00'"></span>
                            </td>
                            <td>
                                <span x-text="
                                    (products.find(p => p.id == item.product_id) && item.quantity) ? 
                                    '$' + (products.find(p => p.id == item.product_id).price_per_unit * item.quantity).toFixed(2) : 
                                    '$0.00'
                                "></span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" x-on:click="lineItems.splice(index, 1)" x-show="lineItems.length > 1">
                                    &times; </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td colspan="2"><strong x-text="'$' + total"></strong></td>
                    </tr>
                </tfoot>
            </table>

            <button type="button" class="btn btn-success btn-sm mb-3" x-on:click="lineItems.push({ product_id: '', quantity: 1 })">
                + Add Product
            </button>
            
            {{-- --- FIN SECCIÓN PRODUCTOS --- --}}
            
            <hr>
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Create Order</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection