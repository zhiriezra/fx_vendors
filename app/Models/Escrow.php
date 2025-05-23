<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escrow extends Model
{
    use HasFactory;

    protected $guarded = [''];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order_processings()
    {
        return $this->hasMany(OrderProcessing::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
