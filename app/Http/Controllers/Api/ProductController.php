<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\ProductImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
// Image Intervention
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use PDO;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = auth()->user()->vendor->products->map(function($product){

            // Retrieve the first image for the product
            $firstImage = $product->images()->first();

            // Generate the full URL for the image if it exists
            $fullImagePath = $firstImage ? url($firstImage->image_path) : null;
            
            return [
                'id' => $product->id,
                'category_id' => $product->category->id,
                'category' => $product->category->name,
                'sub_category_id' => $product->subcategory->id,
                'sub_category' => $product->subcategory->name,
                'vendor_id' => $product->vendor_id,
                'vendor' => $product->vendor->user->firstname.' '.$product->vendor->user->lastname,
                'unit_id' => $product->unit_id,
                'unit' => $product->unit ? $product->unit->name : null,
                'manufacturer' => $product->manufacturer,
                'name' => $product->name,
                'batch_number' => $product->batch_number,
                'quantity' => $product->quantity,
                'images' => $fullImagePath,
                'unit_price' => $product->unit_price,
                'agent_price' => $product->agent_price,
                'description' => $product->description,
                'stock_date' => $product->stock_date,
                'created_at' => Carbon::parse($product->created_at)->format('M j, Y, g:ia'),
                'updated_at' => Carbon::parse($product->updated_at)->format('M j, Y, g:ia')
            ];
        });
        if($products){
            return response()->json(['status' => true, 'message' => 'Product list', 'data' => ['products' => $products]], 200);
        }else{
            return response()->json(['status' => false, 'message' => "Something went wrong!"], 500);
        }

    }

    //Export user products 
    public function export()
    {
        $fileName = 'products_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new ProductsExport, $fileName);  
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
            'name' => 'required',
            'category_id' => 'required',
            'sub_category_id' => 'required',
            'quantity' => 'required|integer',
            'unit_id' => 'required',
            'unit_price' => 'required|numeric',
            'agent_price' => 'required|numeric',
            'description' => 'nullable',
            'manufacturer' => 'required',
            'stock_date' => 'required|date',
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $product = Product::create([
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'vendor_id' => auth()->user()->vendor->id,
            'manufacturer' => $request->manufacturer,
            'name' => $request->name,
            'batch_number' => (string) Str::uuid(),
            'quantity' => $request->quantity,
            'unit_id' => $request->unit_id,
            'unit_price' => $request->unit_price,
            'agent_price' => $request->agent_price,
            'description' => $request->description,
            'stock_date' => $request->stock_date,

        ]);
        

        if($product){

            return response()->json(['status' => true, 'message' => "Your product have been successfully uploaded", "data" => ["product" => $product]], 200);
        }else{

            return response()->json(['status' => false, 'message' => "Something went wrong!"], 500);
        }

    }

    public function addImage(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'images' => 'required|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Check the number of images already uploaded for the product
        $ImagesCount = ProductImage::where('product_id', $request->product_id)->count();

        if ($ImagesCount >= 5) {
            return response()->json(['status' => false, 'message' => "Image must not be more than 5"], 422);
        }

        if ($request->file('images')) {
            $image = $request->file('images');
            $imageName = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());
            $imagePath = $image->storeAs('product_images', $imageName, 'public');

            // Generate the full URL for the image
            $fullImagePath = url(Storage::url($imagePath));

            $productImage = ProductImage::create([
                'product_id' => $request->product_id,
                'image_path' => $fullImagePath
            ]);

            if ($productImage) {
                // Recalculate the image count after adding the new image
                $updatedImagesCount = ProductImage::where('product_id', $request->product_id)->count();
                return response()->json([
                    'status' => true,
                    'message' => "Image Added Successfully",
                    'data' => [
                        'image' => url($fullImagePath), 
                        'ImagesCount' => $updatedImagesCount
                    ]
                ], 200);
            } else {
                return response()->json(['status' => false, 'message' => "Something went wrong!"], 500);
            }
        }
    }

    public function deleteImage(Request $request){
        $validator = Validator::make($request->all(), [
            'image_id' => 'required|exists:product_images,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
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
        // Retrieve the first image for the product
        $firstImage = $product->images()->first();

        // Generate the full URL for the image if it exists
        $fullImagePath = $firstImage ? url($firstImage->image_path) : null;

        if($product){
            $product = [
                'id' => $product->id,
                'category_id' => $product->category->id,
                'category' => $product->category->name,
                'sub_category_id' => $product->subcategory->id,
                'sub_category' => $product->subcategory->name,
                'vendor_id' => $product->vendor_id,
                'vendor' => $product->vendor->user->firstname.' '.$product->vendor->user->lastname,
                'unit_id' => $product->unit_id,
                'unit' => $product->unit ? $product->unit->name : null,
                'manufacturer' => $product->manufacturer,
                'name' => $product->name,
                'first_image' => $fullImagePath,
                'images' => $product->images()->get()->map(function($image){
                    return [
                        'id' => $image->id,
                        'image_path' => url($image->image_path),
                    ];
                }),
                'batch_number' => $product->batch_number,
                'quantity' => $product->quantity,
                'unit_price' => $product->unit_price,
                'agent_price' => $product->agent_price,
                'description' => $product->description,
                'stock_date' => $product->stock_date,
                'created_at' => Carbon::parse($product->created_at)->format('M j, Y, g:ia'),
                'updated_at' => Carbon::parse($product->updated_at)->format('M j, Y, g:ia')
            ];

            return response()->json(['status' => true, 'message' => "Product details", "data" => ['product' => $product]], 200);

        }else{
            return response()->json(['status' => 404, 'message' => "Product Not Found!"], 404);
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
            'unit_id' => 'required',
            'manufacturer' => 'required',
            'name' => 'required',
            'quantity' => 'required|integer',
            'unit_price' => 'required|numeric',
            'agent_price' => 'required|numeric',
            'description' => 'nullable',
            'stock_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $product = Product::find($request->product_id);

        if($product){

            $product->update([
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'vendor_id' => Auth::user()->vendor->id,
                'unit_id' => $request->unit_id,
                'manufacturer' => $request->manufacturer,
                'name' => $request->name,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'agent_price' => $request->agent_price,
                'description' => $request->description,
                'quantity' => $request->quantity,
                'stock_date' => $request->stock_date,
            ]);

            return response()->json(['status' => true,'message' => "Product Updated Successfully", "data" => ['product' => $product]], 200);

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
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }else{

            $category = Category::create([
                'name' => $request->name,
                'status' => 0
            ]);

        }

        if($category){
            return response()->json(['status' => true, 'message' => "Request Sent successfully", "data" => $category], 200);
        }else{
            return response()->json(['status' => false, 'message' => "Something went wrong!"], 500);
        }


    }

    public function productStats() 
    {
        $vendor = Auth::user()->vendor;

        if (!$vendor) {
            return response()->json(['status' => false, 'message' => 'Vendor not found'], 404);
        }

        // product statistics
        $products = $vendor->products()->selectRaw('
            COUNT(*) as total_products,
            SUM(quantity) as available_stocks,
            SUM(quantity * unit_price) as product_value
        ')->first();

        // Return the response
        return response()->json([
            'status' => true,
            'message' => 'Product statistics retrieved successfully',
            'data' => [
                'total_products' => $products->total_products ?? 0,
                'available_stocks' => $products->available_stocks ?? 0,
                'product_value' => $products->product_value ?? 0,
            ]
        ], 200);

    }

    public function lowStockProducts()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return response()->json(['status' => false, 'message' => 'Vendor not found'], 404);
        }

        // Defining low stock threshold
        $lowStockThreshold = 3;

        // getting products with low stock
        $lowStockProducts = $vendor->products()
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', $lowStockThreshold)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Low stock products',
            'data' => [
                'low_stock_products' => $lowStockProducts
            ]
        ], 200);

    }

    public function outOfStockProducts()
    {
        $vendor = auth()->user()->vendor;
    
        if (!$vendor) {
            return response()->json(['status' => false, 'message' => 'Vendor not found'], 404);
        }
    
        // get products that are out of stock
        $outOfStockProducts = $vendor->products()
            ->where('quantity', 0)
            ->get();
    
        return response()->json([
            'status' => true,
            'message' => 'Out of stock products',
            'data' => [
                'out_of_stock_products' => $outOfStockProducts
            ]
        ], 200);
    }

    public function restockProduct(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Find the product
        $product = Product::find($request->product_id);
        
        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Product not found'], 404);
        }

        // Update the product's quantity
        $newQuantity = $product->quantity + $request->quantity;
        $product->update(['quantity' => $newQuantity]);

        return response()->json([
            'status' => true,
            'message' => 'Your product has been successfully restocked',
            'data' => [
                'product' => $product,
                'new_quantity' => $newQuantity
            ]
        ], 200);
    }

    public function inventoryBreakdown()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return response()->json(['status' => false, 'message' => 'Vendor not found'], 404);
        }

        // Fetch inventory breakdown grouped by category
        $inventoryBreakdown = $vendor->products()
            ->selectRaw('
                categories.name as category_name,
                SUM(products.quantity) as total_quantity,
                SUM(products.quantity * products.unit_price) as total_amount
            ')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.name')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Inventory breakdown retrieved successfully',
            'data' => [
                'inventory_breakdown' => $inventoryBreakdown
            ]
        ], 200);
    }
}