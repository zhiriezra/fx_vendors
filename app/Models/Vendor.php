<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

        public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Product::class, 'vendor_id', 'product_id', 'id', 'id');
    }

    public function payoutsRequests()
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function escrow(){
        return $this->hasMany(Escrow::class);
    }

    public function vendorBank()
    {
        return $this->belongsTo(Bank::class, 'bank');
    }
}


