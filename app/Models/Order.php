<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'created_by',
        'delivery_address',
        'notes',
        'status',
        'is_deleted',
        'order_date',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos()
    {
        return $this->hasMany(OrderPhoto::class);
    }

    public function deliveryPhoto()
    {
        return $this->hasOne(OrderPhoto::class)->where('photo_type', 'delivered');
    }
}
