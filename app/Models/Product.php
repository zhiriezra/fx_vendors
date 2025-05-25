<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\StockTracker;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['category_id', 'sub_category_id', 'manufacturer_product_id', 'quantity','unit_id','unit_price','agent_price', 'quantity', 'stock_date', 'vendor_id'];

    protected $casts = [
        'images' => 'array', // Cast images to an array
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }

    // public function subcategory()
    // {
    //     return $this->belongsTo(SubCategory::class, 'sub_category_id');
    // }

    public function manufacturer_product()
    {
        return $this->belongsTo(ManufacturerProduct::class);
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class)->through('manufacturer_product');
    }

    public function orders() {
        return $this->hasMany(Order::class);
    }

    public function product_images() {
        return $this->hasMany(ProductImage::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function low_stock(){
        $stock_tracker = StockTracker::first();
        if($this->quantity > $stock_tracker->quantity){
            return false;
        }else{
            return true;
        }
    }

/*     public function escrow(){
        return $this->hasMany(Escrow::class);
    } */

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
