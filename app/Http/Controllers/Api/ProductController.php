<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use PDO;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\ApiResponder;
use App\Models\Manufacturer;
use App\Models\StockTracker;


class ProductController extends Controller
{
    use ApiResponder;
    
    public function index()
    {
        $vendor = auth()->user()->vendor;

        if(!$vendor){
            return $this->error('Vendor not found', 404);
        }

        $products = $vendor->products->map(function($product){

            return [
                'id' => $product->id,
                'manufacturer' => $product->manufacturer_product->manufacturer->name,
                'category' => $product->manufacturer_product->sub_category->category->name,
                'subcategory' => $product->manufacturer_product->sub_category->name,
                'image' => $product->manufacturer_product->image,   
                'name' => $product->manufacturer_product->name,
                'unit' => $product->unit->name,
                'quantity' => $product->quantity,
                'unit_price' => $product->unit_price,
                'agent_price' => $product->agent_price,
                'low_stock' => $product->low_stock(),
                'created_at' => Carbon::parse($product->created_at)->format('M j, Y, g:ia'),
                'updated_at' => Carbon::parse($product->updated_at)->format('M j, Y, g:ia')
            ];
        });

        if($products){
            return $this->success($products, 'Product list', 200);
        }else{
            return $this->error('Something went wrong!', 500);
        }

    }

    //Export user products 
    public function export()
    {
        $fileName = 'products_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new ProductsExport, $fileName);  
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'manufacturer_product_id' => 'required',
            'quantity' => 'required|integer',
            'unit_id' => 'required',
            'unit_price' => 'required|numeric',
            'agent_price' => 'required|numeric|lt:unit_price',
            'stock_date' => 'required|date',
        ]);

        

        if ($validator->fails()) {
            return $this->validation($validator->errors(), $validator->errors()->first(), 422);
        }

        $product = Product::create([
            'category_id' => 1, //not in use
            'sub_category_id' => 1, //not in use
            'vendor_id' => auth()->user()->vendor->id,
            'manufacturer_product_id' => $request->manufacturer_product_id,
            'batch_number' => (string) Str::uuid(),
            'quantity' => $request->quantity,
            'unit_id' => $request->unit_id,
            'unit_price' => $request->unit_price,
            'agent_price' => $request->agent_price,
            'stock_date' => $request->stock_date,

        ]);
        
        if($product){
            return $this->success(null, 'Product created successfully', 200);
        }else{
            return $this->error('Something went wrong!', 500);
        }

    }

    public function show($id)
    {
        $product = Product::with('manufacturer_product')->find($id);
        if(!$product){
            return $this->error("Product Not Found!", 404);
        }

        $product = [
            'id' => $product->id,
            'manufacturer_product_id' => $product->manufacturer_product_id,
            'manufacturer' => $product->manufacturer_product->manufacturer->name,
            'category' => $product->manufacturer_product->sub_category->category->name,
            'subcategory' => $product->manufacturer_product->sub_category->name,
            'image' => $product->manufacturer_product->image,   
            'name' => $product->manufacturer_product->name,
            'description' => $product->manufacturer_product->description,
            'unit_id' => $product->unit_id,
            'unit' => $product->unit->name,
            'batch_number' => $product->batch_number,
            'quantity' => $product->quantity,
            'unit_price' => $product->unit_price,
            'agent_price' => $product->agent_price,
            'stock_date' => $product->stock_date,
            'low_stock' => $product->low_stock(),
            'created_at' => Carbon::parse($product->created_at)->format('M j, Y, g:ia'),
            'updated_at' => Carbon::parse($product->updated_at)->format('M j, Y, g:ia')
        ];

        return $this->success($product, "Product details", 200);

      
    }

    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'manufacturer_product_id' => 'required',
            'quantity' => 'required|integer',
            'unit_id' => 'required',
            'unit_price' => 'required|numeric',
            'agent_price' => 'required|numeric',
            'stock_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }


        $product = Product::find($request->product_id);

        if($product){

            $product->update([
                'product_id' => $request->product_id,
                'manufacturer_product_id' => $request->manufacturer_product_id,
                'quantity' => $request->quantity,
                'unit_id' => $request->unit_id,
                'unit_price' => $request->unit_price,
                'agent_price' => $request->agent_price,
                'stock_date' => $request->stock_date,
            ]);

            return $this->success(null, 'Product updated successfully', 200);
        }else{
            return $this->error('Product not found', 404);
        }
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if($product){
            $product->delete();
            return $this->success(null, 'Product removed successfully', 200);
        }else{
            return $this->error('Product not found', 404);
        }
    }

    public function productStats() 
    {
        $vendor = Auth::user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor not found', 404);
        }

        // product statistics
        $products = $vendor->products()->selectRaw('
            COUNT(*) as total_products,
            SUM(quantity) as available_stocks,
            SUM(quantity * unit_price) as product_value
        ')->first();

        // Return the response
        return $this->success([
            'total_products' => $products->total_products ?? 0,
            'available_stocks' => $products->available_stocks ?? 0,
            'product_value' => $products->product_value ?? 0,
        ], 'Product statistics retrieved successfully', 200);

    }

    public function inventoryBreakdown()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor not found', 404);
        }

        // Get products grouped by manufacturer product category
        $products = $vendor->products()
            ->with(['manufacturer_product.sub_category.category'])
            ->get()
            ->groupBy(function($product) {
                return $product->manufacturer_product->sub_category->category->name;
            })
            ->map(function($categoryProducts, $categoryName) {
                return [
                    'category' => $categoryName,
                    'total_products' => $categoryProducts->count(),
                    'total_quantity' => $categoryProducts->sum('quantity'),
                    'total_value' => $categoryProducts->sum(function($product) {
                        return $product->quantity * $product->unit_price;
                    }),
                
                ];
            })
            ->values();

        return $this->success(['inventory_breakdown' => $products], 'Inventory breakdown', 200);

    }

    public function manufacturerProducts()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor not found', 404); 
        }

        $manufacturers = Manufacturer::with('manufacturer_products')->get();

        $manufacturers = $manufacturers->map(function($manufacturer) {
            return [
                'id' => $manufacturer->id,
                'name' => $manufacturer->name,
                'products' => $manufacturer->manufacturer_products->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name
                    ];
                })
            ];
        });
        return $this->success($manufacturers, 'Manufacturers & products', 200);
    }

    public function lowStockProducts()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor not found', 404);
        }

        // Defining low stock threshold
        $stock_tracker = StockTracker::first();

        // getting products with low stock
        $lowStockProducts = $vendor->products()
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', $stock_tracker->quantity)
            ->with(['manufacturer_product.manufacturer', 'manufacturer_product.sub_category.category']) // Eager load relationships
            ->get();

            $products = $lowStockProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'manufacturer' => $product->manufacturer_product->manufacturer->name,
                    'category' => $product->manufacturer_product->sub_category->category->name,
                    'subcategory' => $product->manufacturer_product->sub_category->name,
                    'image' => $product->manufacturer_product->image,   
                    'name' => $product->manufacturer_product->name,
                    'description' => $product->manufacturer_product->description,
                    'unit' => $product->unit->name,
                    'batch_number' => $product->batch_number,
                    'quantity' => $product->quantity,
                    'unit_price' => $product->unit_price,
                    'agent_price' => $product->agent_price,
                    'stock_date' => $product->stock_date,
                    'low_stock' => $product->low_stock(),
                    'created_at' => Carbon::parse($product->created_at)->format('M j, Y, g:ia'),
                    'updated_at' => Carbon::parse($product->updated_at)->format('M j, Y, g:ia')
                ];
            });

        return $this->success(['low_stock_products' => $products], 'Low stock products', 200);

    }

    public function outOfStockProducts()
    {
        $vendor = auth()->user()->vendor;
    
        if (!$vendor) {
            return $this->error('Vendor not found', 404);
        }
    
        // get products that are out of stock
        $outOfStockProducts = $vendor->products()
            ->where('quantity', 0)
            ->with(['manufacturer_product.manufacturer', 'manufacturer_product.sub_category.category']) // Eager load relationships
            ->get();

            $products = $outOfStockProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'manufacturer' => $product->manufacturer_product->manufacturer->name,
                    'category' => $product->manufacturer_product->sub_category->category->name,
                    'subcategory' => $product->manufacturer_product->sub_category->name,
                    'image' => $product->manufacturer_product->image,   
                    'name' => $product->manufacturer_product->name,
                    'description' => $product->manufacturer_product->description,
                    'unit' => $product->unit->name,
                    'batch_number' => $product->batch_number,
                    'quantity' => $product->quantity,
                    'unit_price' => $product->unit_price,
                    'agent_price' => $product->agent_price,
                    'stock_date' => $product->stock_date,
                    'low_stock' => $product->low_stock(),
                    'created_at' => Carbon::parse($product->created_at)->format('M j, Y, g:ia'),
                    'updated_at' => Carbon::parse($product->updated_at)->format('M j, Y, g:ia')
                ];
            });
    
        return $this->success(['out_of_stock_products' => $products], 'Out of stock products', 200);
    }

    public function restockProduct(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        // Find the product
        $product = Product::with(['manufacturer_product.manufacturer', 'manufacturer_product.sub_category.category'])->find($request->product_id);
        
        if (!$product) {
            return $this->error('Product not found', 404);
        }

        // Update the product's quantity
        $newQuantity = $product->quantity + $request->quantity;
        $product->update(['quantity' => $newQuantity]);

        $formattedProduct = [
            'id' => $product->id,
            'manufacturer' => $product->manufacturer_product->manufacturer->name,
            'category' => $product->manufacturer_product->sub_category->category->name,
            'subcategory' => $product->manufacturer_product->sub_category->name,
            'image' => $product->manufacturer_product->image,   
            'name' => $product->manufacturer_product->name,
            'description' => $product->manufacturer_product->description,
            'unit' => $product->unit->name,
            'batch_number' => $product->batch_number,
            'quantity' => $newQuantity,
            'unit_price' => $product->unit_price,
            'agent_price' => $product->agent_price,
            'stock_date' => $product->stock_date,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];

        return $this->success(['product' => $formattedProduct, 'new_quantity' => $newQuantity], 'Product restocked successfully', 200);
    }
    
}
