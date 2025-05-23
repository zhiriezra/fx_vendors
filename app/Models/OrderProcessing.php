<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProcessing extends Model
{
    use HasFactory;

    protected $fillable = [
        'escrow_id',
        'stage'
    ];

    public function escrow()
    {
        return $this->belongsTo(Escrow::class);
    }
}
