<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;

class TrackingController extends Controller
{
    public function index()
    {
        return view('rastreo');
    }

    public function search(Request $request)
    {
        $request->validate([
            'customer_number' => 'required|string',
            'invoice_number'  => 'required|string',
        ]);

        // Buscar cliente por número de cliente
        $customer = Customer::where('customer_number', $request->customer_number)->first();

        if (!$customer) {
            return back()->with('error', 'No se encontró ningún cliente con ese número.');
        }

// Buscar la orden vinculada a ese cliente y número de factura
        $order = $customer->orders()
            ->where('invoice_number', $request->invoice_number)
            ->with(['photos', 'products']) // ¡AQUÍ ESTÁ EL CAMBIO! Cargamos los productos
            ->first();

        if (!$order) {
            return back()->with('error', 'No se encontró ninguna orden con ese número de factura para este cliente.');
        }

        return view('rastreo', compact('order', 'customer'));
    }
}
