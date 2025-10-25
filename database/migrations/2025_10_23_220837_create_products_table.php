<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Título del producto
            $table->text('description')->nullable();
            
            // Usamos 'decimal' para precios. 
            // 10 dígitos totales, 2 decimales (ej. 12345678.99)
            $table->decimal('price_per_unit', 10, 2); 
            
            // Ruta de la imagen
            $table->string('image_path')->nullable(); 
            
            $table->boolean('is_active')->default(true); // Útil para "desactivar" un producto sin borrarlo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};