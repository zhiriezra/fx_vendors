<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Auth; 

// Image Intervention
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::where('vendor_id', Auth::id())->get();

        if($products){

            return response()->json([
                'status' => 200,
                'products' => $products
            ], 200);
        }else{

            return response()->json([
                'status' => 500,
                'message' => "Something went wrong!"
            ], 500);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'type' => 'required',
            'manufacturer' => 'required',
            'name' => 'required',
            'quantity' => 'required|integer',
            'unit_price' => 'required|numeric',
            'agent_price' => 'required|numeric',
            'description' => 'nullable',
            'stock_date' => 'required|date',
            'images.*' => 'image|max:2048'
        ]);

        if($validator->fails()){

            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }else{

            $product = Product::create([
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'vendor_id' => Auth::id(),
                'type' => $request->type,
                'manufacturer' => $request->manufacturer,
                'name' => $request->name,
                'batch_number' => (string) Str::uuid(),
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'agent_price' => $request->agent_price,
                'description' => $request->description,
                'quantity' => $request->quantity,
                'stock_date' => $request->stock_date,

            ]);

            if ($request->file('images')) {
                foreach ($request->file('images') as $image) {
                    $manager = new ImageManager(new Driver());
                    $name_gen = hexdec(uniqid()).'.'.$request->file('images')->getClientOriginalExtension();
                    $img = $manager->read($request->file('images'));
                    $img = $img->resize(370,246);
    
                    $img->toJpeg(80)->save(base_path('public/storage/product_images/'.$name_gen));
                    $save_url = 'product_images/'.$name_gen;
    
                    // Save image path to the database
                    $productImage = ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => 'product_images/'.$filename,
                    ]);
                }
            }
        }

        if($product && $productImage){

            return response()->json([
                'status' => 201,
                'message' => "Product Created Successfully"
            ], 201);
        }else{

            return response()->json([
                'status' => 500,
                'message' => "Something went wrong!"
            ], 500);
        } 

    }

    public function addImage(Request $request , $id)
    {
        $product = Product::find($id);
        $validator = Validator::make($request->all(), [
            'images.*' => 'image|max:2048'
        ]);

        if($validator->fails()){

            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }else{

            if ($request->file('images')) {
                    $manager = new ImageManager(new Driver());
                    $name_gen = hexdec(uniqid()).'.'.$request->file('images')->getClientOriginalExtension();
                    $img = $manager->read($request->file('images'));
                    $img = $img->resize(370,246);
    
                    $img->toJpeg(80)->save(base_path('public/storage/product_images/'.$name_gen));
                    $save_url = 'product_images/'.$name_gen;
    
                    // Save image path to the database
                    $productImage = ProductImage::insert([
                        'product_id' => $product->id,
                        'image_path' => $save_url,
                    ]);
                }
                if($productImage){

                    return response()->json([
                        'status' => 201,
                        'message' => "Image Added Successfully"
                    ], 201);
                }else{
        
                    return response()->json([
                        'status' => 500,
                        'message' => "Something went wrong!"
                    ], 500);
                }   
            }            
       
        

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);

        if($product){

            return response()->json([
                'status' => 200,
                'product' => $product
            ], 200);
            
        }else{
            return response()->json([
                'status' => 404,
                'message' => "Product Not Found!"
            ], 404);

        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'type' => 'required',
            'manufacturer' => 'required',
            'name' => 'required',
            'quantity' => 'required|integer',
            'unit_price' => 'required|numeric',
            'agent_price' => 'required|numeric',
            'description' => 'nullable',
            'stock_date' => 'required|date',
            'images.*' => 'image|max:2048'
        ]);

        if($validator->fails()){

            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }else
        
        {

            $product = Product::find($id);
            if($product){

                $product -> update([
                    'category_id' => $request->category_id,
                    'subcategory_id' => $request->subcategory_id,
                    'vendor_id' => Auth::id(),
                    'type' => $request->type,
                    'manufacturer' => $request->manufacturer,
                    'name' => $request->name,
                    'quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'agent_price' => $request->agent_price,
                    'description' => $request->description,
                    'quantity' => $request->quantity,
                    'stock_date' => $request->stock_date,
    
                ]);

                if ($request->has('images')) {
                    foreach ($request->file('images') as $image) {
                        $path = $image->store('product_images', 'public');
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $path
                        ]);
                    }
                }    

                return response()->json([
                    'status' => 201,
                    'message' => "Product Updated Successfully"
                ], 201);
            }else
            
            {
                return response()->json([
                    'status' => 404,
                    'message' => "Product Not Found!"
                ], 404);
            }
        }

   }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if($product){
            
            $product->delete();
            return response()->json([
                'status' => 200,
                'message' => "Product Deleted Successfully"
            ], 404);

        }else{
            return response()->json([
                'status' => 404,
                'message' => "Product Not Found!"
            ], 404);

        }
    }

    public function CatRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            
        ]);

        if($validator->fails()){

            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }else{

            $category = Category::create([
                'name' => $request->name,
                'status' => 0

            ]);

            }

        if($category){

            return response()->json([
                'status' => 201,
                'message' => "Request Sent"
            ], 201);
        }else{

            return response()->json([
                'status' => 500,
                'message' => "Something went wrong!"
            ], 500);
        } 


    }
}
