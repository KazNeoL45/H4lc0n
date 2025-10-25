<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El nombre es la convención de Laravel: 'order_product' (singular, alfabético)
        Schema::create('order_product', function (Blueprint $table) {
            $table->id();
            
            // Clave foránea a 'orders'
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            // Clave foránea a 'products'
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // Campos adicionales de la relación
            $table->integer('quantity'); // La cantidad que pidieron
            
            // ¡CRÍTICO! Guarda el precio unitario al momento de la compra.
            // No uses el precio de la tabla 'products', porque ese puede cambiar en el futuro.
            $table->decimal('unit_price', 10, 2); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_product');
    }
};