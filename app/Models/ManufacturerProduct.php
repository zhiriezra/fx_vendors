<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManufacturerProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacturer_id',
        'sub_category_id',
        'name',
        'description',
        'image',    
        'status',
    ];

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    
    public function sub_category(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }
    
}
