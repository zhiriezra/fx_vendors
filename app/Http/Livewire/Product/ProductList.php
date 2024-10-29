<?php

namespace App\Http\Livewire\Product;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductList extends Component
{

    use WithPagination;

    // Set the pagination theme to Bootstrap
    protected $paginationTheme = 'bootstrap';


    public $images;
    public $search = "";

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

    public function exportExcel()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function render()
    {
        $vendorIds = Auth::user()->vendor->id;
            $products = Product::with(['category', 'subcategory'])
            ->where('vendor_id', $vendorIds)  // Limit to vendors in the same state
            ->when(strlen($this->search) >= 2, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('batch_number', 'like', '%' . $this->search . '%')
                    ->orWhere('manufacturer', 'like', '%' . $this->search . '%')
                    ->orWhereHas('category', function($search) {
                        $search->where('name', 'like', '%' . $this->search . '%');
                    });
            })->paginate(20); 


        return view('livewire.product.product-list',[
            'images' => $this->images,
            'products' => $products
        ]);
    }
}
