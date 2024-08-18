<?php

namespace App\Http\Livewire\Product;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Auth;

class ProductList extends Component
{

    public $products = [];
    public $images;

    public function mount()
    {
        $vendor = Auth::user()->vendor;
        if ($vendor) {
            $this->products = Product::where('vendor_id', $vendor->id)->with(['category', 'subcategory'])->get();
        } else {
            session()->flash('error', 'Vendor profile not found.');
        }

        $this->images = ProductImage::get();
    }

    public function softDelete($productId)
    {
        $product = Product::find($productId);
        if ($product) {
            $product->delete();
        }

        $this->products = Product::with(['images', 'category', 'subcategory', 'vendor.user'])->get();
        session()->flash('message', 'Product Deleted!');
    }

    public function render()
    {
        return view('livewire.product.product-list',[
            'images' => $this->images,
            'products' => $this->products
        ]);
    }
}
