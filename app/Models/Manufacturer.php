<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'logo',
        'website',
        'description',
    ];

    public function manufacturer_products()
    {
        return $this->hasMany(ManufacturerProduct::class);
    }
}
