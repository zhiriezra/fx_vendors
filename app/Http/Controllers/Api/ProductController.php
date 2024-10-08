<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

// Image Intervention
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use PDO;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::where('vendor_id', Auth::user()->vendor->id)->get();

        if($products){
            return response()->json(['status' => true, 'message' => 'Product list', 'data' => ['products' => $products]], 200);
        }else{
            return response()->json(['status' => false, 'message' => "Something went wrong!"], 500);
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
            'sub_category_id' => 'required',
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


        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Invalid details provided', 'errors' => $validator->errors()], 422);
        }

        $product = Product::create([
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'vendor_id' => auth()->user()->vendor->id,
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

        if($product){
            return response()->json(['status' => true, 'message' => "Product Created Successfully", "data" => ["product" => $product]], 201);
        }else{

            return response()->json(['status' => false, 'message' => "Something went wrong!"], 500);
        }

    }

    public function addImage(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required',
            'images.*' => 'image|max:2048'
        ]);


        if($request->file('images')) {
            $manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()).'.'.$request->file('images')->getClientOriginalExtension();
            $img = $manager->read($request->file('images'));
            $img = $img->resize(370,246);

            $img->toJpeg(80)->save(base_path('public/storage/product_images/'.$name_gen));
            $save_url = 'product_images/'.$name_gen;

            // Save image path to the database
            $productImage = ProductImage::insert(['product_id' => $request->product_id, 'image_path' => $save_url]);
        }
        if($productImage){
            return response()->json(['status' => true, 'message' => "Image Added Successfully", 'data' => $productImage], 201);
        }else{
            return response()->json(['status' => false, 'message' => "Something went wrong!"], 500);
        }

    }

    public function deleteImage(Request $request){
        $validator = Validator::make($request->all(), [
            'image_id' => 'required|exists:product_images,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Invalid details provided', 'errors' => $validator->errors()], 422);
        }

        $image = ProductImage::find($request->image_id);

        if($image){
            $image->delete();
            return response()->json(['status' => true, 'message' => 'Deleted successfully'], 204);
        }else{
            return response()->json(['status' => false, 'message' => 'Image not found'], 404);
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
        $product = Product::with('product_images')->find($id);

        if($product){

            return response()->json(['status' => 200, "data" => ['product' => $product]], 200);

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
    public function update(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'category_id' => 'required',
            'sub_category_id' => 'required',
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

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Invalid details provided', 'errors' => $validator->errors()], 422);
        }

        $product = Product::find($request->product_id);

        if($product){

            $product->update([
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'vendor_id' => Auth::user()->vendor->id,
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

            return response()->json(['status' => true,'message' => "Product Updated Successfully"], 201);

        }else{

            return response()->json(['status' => false,'message' => "Product Not Found!"], 404);
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

    public function categories(){
        $categories = Category::where('status', 1)->get()->makeHidden(['created_at', 'deleted_at','updated_at']);
        if($categories){
            return response()->json(['status' => true, 'message' => "Category list", "data" => ["categories" => $categories]], 200);
        }else{
            return response()->json(['status' => false, 'message' => "No available categories!"], 404);
        }
    }

    public function category($id){
        $category = Category::where(['status' => 1, 'id' => $id])->with('subcategories')->first()->makeHidden(['created_at', 'deleted_at','updated_at']);
        if($category){
            return response()->json(['status' => true, 'message' => "Category", "data" => ["category" => $category]], 200);
        }else{
            return response()->json(['status' => false, 'message' => "Can not find category!"], 404);
        }
    }

    public function CatRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',

        ]);

        if($validator->fails()){
            return response()->json(['status' => false, 'message' => 'Invalid details provided', 'errors' => $validator->messages() ], 422);
        }else{

            $category = Category::create([
                'name' => $request->name,
                'status' => 0
            ]);

        }

        if($category){
            return response()->json(['status' => true, 'message' => "Request Sent successfully", "data" => $category], 201);
        }else{
            return response()->json(['status' => false, 'message' => "Something went wrong!"], 500);
        }


    }
}
