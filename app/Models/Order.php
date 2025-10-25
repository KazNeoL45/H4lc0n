<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'created_by',
        'delivery_address',
        'notes',
        'status',
        'is_deleted',
        'order_date',
        'total_amount',
    ];

    /**
     * LA CORRECCIÓN:
     * Indica a Eloquent que trate estas columnas como tipos específicos.
     * 'order_date' se convertirá automáticamente en un objeto Carbon.
     */
    protected $casts = [
        'order_date' => 'datetime',
    ];


    /**
     * Relación: Una orden tiene un cliente.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación: Una orden fue creada por un usuario.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación: Una orden tiene (muchos) productos.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot('quantity', 'unit_price') // ¡Importante!
                    ->withTimestamps();
    }
    
    /**
     * Relación: Una orden tiene muchas fotos (tu migración order_photos)
     */
    public function photos()
    {
        return $this->hasMany(OrderPhoto::class);
    }
}
