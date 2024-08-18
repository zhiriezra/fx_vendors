<?php

namespace App\Http\Livewire\Product;

use Livewire\Component;
use App\Models\ProductImage;
use Livewire\WithFileUploads;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductShow extends Component
{

    use WithFileUploads;

    public $images = [];
    public $productImage;
    public $product;
    public $product_id;
    public $newImage;
    public $showForm = false;
    public $maxImages = 5;

    public function mount($productImage, $product)
    {
        $this->productImage = $productImage;
        $this->product = $product;
    }

    public function showForm()
    {
        $this->showForm = true;
    }

    public function hideForm()
    {
        $this->showForm = false;
    }

    public function addImage()
    {
        $this->validate([
            'newImage' => 'image|max:1024', // 1MB Max per image
        ]);

        // Ensuring Image is not more than 5
        if (count($this->images) > $this->maxImages) {
            $this->addError('images', 'You cannot upload more than 5 images.');
            return;
        }

        if ($this->newImage) {
            
                $manager = new ImageManager(new Driver());

                $name_gen = hexdec(uniqid()).'.'.$this->newImage->getClientOriginalExtension();
                $img = $manager->read($this->newImage);
                $img = $img->resize(370,246);

                $img->toJpeg(80)->save(base_path('public/storage/product_images/'.$name_gen));
                $save_url = 'product_images/'.$name_gen;

                // Save image path to the database
                $productImage = ProductImage::create([
                    'product_id' => $this->product->id,
                    'image_path' => $save_url,
                ]);
        }

        session()->flash('message', 'Image Added!');
           
            $this->newImage = null;
            $this->hideForm();
            $this->productImage = ProductImage::all();
    }

    public function deleteImage($id)
    {
        $image = ProductImage::findOrFail($id);
        \Storage::disk('public')->delete($image->image_path);
        $image->delete();
        session()->flash('message', 'Image Deleted!');
        $this->productImage = ProductImage::all();
    }
    
    public function render()
    {
        return view('livewire.product.product-show');
    }
}
