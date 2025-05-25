<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function escrow()
    {
        return $this->hasOne(Escrow::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
