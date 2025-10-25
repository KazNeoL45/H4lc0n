@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Rastreo de Paquete</h1>

    {{-- Formulario de búsqueda --}}
    <form method="POST" action="{{ route('rastreo.buscar') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="customer_number" class="form-label">Número de cliente</label>
                <input type="text" name="customer_number" id="customer_number" class="form-control"
                       placeholder="Ej. CUST-00123" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="invoice_number" class="form-label">Número de factura</label>
                <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                       placeholder="Ej. FAC-00123" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>

    {{-- Mensajes --}}
    @if(session('error'))
        <div class="alert alert-danger mt-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Resultado del rastreo --}}
    @isset($order)
        <div class="card mt-5">
            <div class="card-body">
                <h5 class="card-title">Detalles del Pedido</h5>
                <p><strong>Cliente:</strong> {{ $customer->name }}</p>
                <p><strong>Número de cliente:</strong> {{ $customer->customer_number }}</p>
                <p><strong>Factura:</strong> {{ $order->invoice_number }}</p>
                <p><strong>Dirección de entrega:</strong> {{ $order->delivery_address }}</p>
                <p><strong>Fecha de pedido:</strong> {{ $order->order_date->format('d/m/Y H:i') }}</p>
                <p><strong>Estatus:</strong>
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

                {{-- --- SECCIÓN DE PRODUCTOS (MODIFICADA) --- --}}
                @if($order->products->count() > 0)
                <h5 class="mt-4">Productos en esta orden</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
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
                            <td colspan="3" class="text-end">Total de la Orden:</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @endif
                {{-- --- FIN SECCIÓN DE PRODUCTOS --- --}}


                {{-- --- SECCIÓN DE FOTOS (MODIFICADA) --- --}}
                @if($order->photos->count() > 0)
                    <div class="mt-4">
                        <h5>Evidencia Fotográfica</h5>
                        <div class="row">
                            @php
                                // Buscamos ambas fotos
                                $loadedPhoto = $order->photos->firstWhere('photo_type', 'loaded');
                                $deliveredPhoto = $order->photos->firstWhere('photo_type', 'delivered');
                            @endphp
                            
                            {{-- Mostramos la foto de Carga si el estatus es 'In route' o 'Delivered' --}}
                            @if($loadedPhoto && in_array($order->status, ['In route', 'Delivered']))
                                <div class="col-md-6 mb-3">
                                    <h6>Foto de Carga (En ruta):</h6>
                                    <img src="{{ asset('storage/' . $loadedPhoto->photo_path) }}" 
                                         alt="Foto de carga" class="img-fluid rounded shadow">
                                </div>
                            @endif

                            {{-- Mostramos la foto de Entrega si el estatus es 'Delivered' --}}
                            @if($deliveredPhoto && $order->status === 'Delivered')
                                <div class="col-md-6 mb-3">
                                    <h6>Evidencia de entrega:</h6>
                                    <img src="{{ asset('storage/' . $deliveredPhoto->photo_path) }}" 
                                         alt="Foto de entrega" class="img-fluid rounded shadow">
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Mensaje si está entregado pero no hay fotos --}}
                    @if($order->status === 'Delivered')
                        <p class="text-muted mt-3">No hay evidencia fotográfica disponible.</p>
                    @endif
                @endif
                {{-- --- FIN SECCIÓN DE FOTOS --- --}}

            </div>
        </div>
    @endisset
</div>
@endsection

