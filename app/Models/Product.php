<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price_per_unit',
        'image_path',
        'is_active',
    ];

    /**
     * Relación: Un producto puede estar en muchas órdenes.
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class)
                    ->withPivot('quantity', 'unit_price') // ¡Importante!
                    ->withTimestamps();
    }
}