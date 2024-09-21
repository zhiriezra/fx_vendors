<?php

namespace App\Http\Livewire\Product;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
// Image Intervention
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductCreate extends Component
{

    use WithFileUploads;

    public $categories;
    public $subcategories = [];
    public $category_id;
    public $subcategory_id;
    public $type;
    public $manufacturer;
    public $name;
    public $batch_number;
    public $quantity;
    public $unit_price;
    public $agent_price;
    public $description;
    public $stock_date;
    public $images = [];
    public $maxImages = 5;
    public $vendor;

    protected $rules = [
        'category_id' => 'required',
        'sub_category_id' => 'required',
        'type' => 'required|string|max:255',
        'manufacturer' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'unit_price' => 'required|numeric|min:0',
        'agent_price' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'stock_date' => 'required|date',
        'images.*' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048', // 2MB Max per image
    ];

    public function mount()
    {
        $this->categories = Category::all();
        $this->vendor = Vendor::where('user_id', Auth::id())->first();
    }

    public function updatedCategoryId($value)
    {
        $this->subcategories = Category::find($value)->subcategories;
    }

    // public function generateBatchNumber()
    // {
    //     return strtoupper(Str::random(8)); // Generates a random 8 character string
    // }

    public function updatedImages()
    {
        if (count($this->images) > $this->maxImages) {
            $this->reset('images');
            $this->addError('images', 'You cannot upload more than 5 images.');
        }
    }

    public function createProduct()
    {
        $this->validate();

         // Generate batch number
        //  $this->batch_number = $this->generateBatchNumber();

         // Ensuring Image is not more than 5
         if (count($this->images) > $this->maxImages) {
            $this->addError('images', 'You cannot upload more than 5 images.');
            return;
        }

        $product = Product::create([
            'category_id' => $this->category_id,
            'sub_category_id' => $this->subcategory_id,
            'vendor_id' => $this->vendor->id,
            'type' => $this->type,
            'manufacturer' => $this->manufacturer,
            'name' => $this->name,
            'batch_number' => $this->batch_number,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'agent_price' => $this->agent_price,
            'description' => $this->description,
            'stock_date' => $this->stock_date
        ]);

        // Image intervention
        if ($this->images) {
            foreach ($this->images as $image) {
                $manager = new ImageManager(new Driver());

                $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
                $img = $manager->read($image);
                $img = $img->resize(370,246);

                $img->toJpeg(80)->save(base_path('public/storage/product_images/'.$name_gen));
                $save_url = url('storage/product_images/'.$name_gen);

                // Save image path to the database
                $productImage = ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $save_url,
                ]);
            }
        }

        return redirect()->route('vendor.product.index')->with('message', 'Product Added!');
    }

    public function render()
    {
        return view('livewire.product.product-create');
    }
}
