<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [''];

    protected $casts = [
        'images' => 'array', // Cast images to an array
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate a random 8-character string
            $randomString = Str::upper(Str::random(8)); // Uppercase for better readability

            // Defining desired suffix
            $suffix = '-FEX'; 

            // Combine the random string and suffix
            $model->batch_number = $randomString . $suffix;
        });
    }
}
