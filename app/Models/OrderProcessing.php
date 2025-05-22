<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProcessing extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'stage'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
