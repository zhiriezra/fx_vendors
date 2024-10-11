<?php

namespace App\Http\Livewire\Product;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Auth;

class ProductEdit extends Component
{
    public $product;
    public $categories = [];
    public $subcategories = [];
    public $category_id, $subcategory_id, $type, $manufacturer, $name;
    public $quantity, $unit_price, $agent_price, $description, $stock_date;
    
    protected $rules = [
        'category_id' => 'required|integer',
        'subcategory_id' => 'required|integer',
        'type' => 'required|string',
        'manufacturer' => 'required|string',
        'name' => 'required|string|max:255',
        'quantity' => 'required|integer',
        'unit_price' => 'required|numeric',
        'agent_price' => 'required|numeric',
        'description' => 'nullable|string',
        'stock_date' => 'required|date',
    ];

    public function mount($product_data)
    {
        //Load Categories and Sub Categories
        $this->categories = Category::where('status', 1)->get();

        // Load the product based on the product ID
        $this->product = $product_data;

        // Populate the fields with existing product data
        $this->category_id = $this->product->category_id;
        $this->subcategory_id = $this->product->sub_category_id;
        $this->type = $this->product->type;
        $this->manufacturer = $this->product->manufacturer;
        $this->name = $this->product->name;
        $this->quantity = $this->product->quantity;
        $this->unit_price = $this->product->unit_price;
        $this->agent_price = $this->product->agent_price;
        $this->description = $this->product->description;
        $this->stock_date = $this->product->stock_date;
        $this->subcategories = SubCategory::where('category_id', $this->category_id)->get();
    }

    public function updatedCategoryId($value)
    {
        $this->subcategories = SubCategory::where('category_id', $value)->get();
        $this->subcategory_id = null;
    }

    public function updateProduct()
    {
        $this->validate();

        // Update the product
        $this->product->update([
            'category_id' => $this->category_id,
            'sub_category_id' => $this->subcategory_id,
            'type' => $this->type,
            'manufacturer' => $this->manufacturer,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'agent_price' => $this->agent_price,
            'description' => $this->description,
            'stock_date' => $this->stock_date,
        ]);

        session()->flash('message', 'Product updated successfully.');
        return redirect()->route('vendor.product.index'); 
    }

    public function render()
    {
        return view('livewire.product.product-edit');
    }
}
