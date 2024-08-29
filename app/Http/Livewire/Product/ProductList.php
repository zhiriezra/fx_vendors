<?php

namespace App\Http\Livewire\Product;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class ProductList extends Component
{

    use WithPagination;

    // Set the pagination theme to Bootstrap
    protected $paginationTheme = 'bootstrap';


    public $images;

    public function mount()
    {

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
        $vendor = Auth::user()->vendor;
        $products = Product::where('vendor_id', $vendor->id)->with(['category', 'subcategory'])->paginate(20);

        return view('livewire.product.product-list',[
            'images' => $this->images,
            'products' => $products
        ]);
    }
}
