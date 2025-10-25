<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // ¡Importante para borrar imágenes!

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * ¡ESTE ES EL MÉTODO QUE FALTABA!
     */
    public function index()
    {
        // Obtiene todos los productos y los pasa a la vista
        $products = Product::orderBy('title')->get();
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Esta es la vista que ya tenías
        return view('admin.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_unit' => 'required|numeric|min:0',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image_path')) {
            $imagePath = $request->file('image_path')->store('imgProductos', 'public');
            $validatedData['image_path'] = $imagePath;
        }

        Product::create($validatedData);

        return redirect()->route('admin.products.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Puedes crear una vista 'show.blade.php' si necesitas ver detalles
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        // Necesitarás una vista 'edit.blade.php' para esto
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_unit' => 'required|numeric|min:0',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // La imagen es opcional al actualizar
        ]);

        if ($request->hasFile('image_path')) {
            // Borrar la imagen anterior si existe
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            // Subir la nueva imagen
            $imagePath = $request->file('image_path')->store('imgProductos', 'public');
            $validatedData['image_path'] = $imagePath;
        }

        $product->update($validatedData);

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Borrar la imagen del almacenamiento
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        // Borrar el registro de la base de datos
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
