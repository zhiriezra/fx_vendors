<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $userId = Auth::user()->vendor->id;

        return Product::with(['manufacturer_product.manufacturer', 'manufacturer_product.sub_category.category', 'unit'])
            ->where('vendor_id', $userId)
            ->get()
            ->map(function($product) {
                return (object)[
                    'name' => $product->manufacturer_product->name, 
                    'batch_number' => $product->batch_number,
                    'manufacturer' => $product->manufacturer_product->manufacturer->name,
                    'category_name' => $product->manufacturer_product->sub_category->category->name,
                    'subcategory_name' => $product->manufacturer_product->sub_category->name,
                    'unit_name' => $product->unit->name,
                    'quantity' => $product->quantity,
                    'unit_price' => $product->unit_price,
                    'agent_price' => $product->agent_price,
                    'description' => $product->manufacturer_product->description,
                    'stock_date' => $product->stock_date
                ];
            });
        
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->batch_number,
            $product->manufacturer,
            $product->category_name,
            $product->subcategory_name,
            $product->unit_name,
            $product->quantity === null ? 0 : $product->quantity,
            number_format($product->unit_price, 2),
            number_format($product->agent_price, 2),
            $product->description,
            Carbon::parse($product->stock_date)->format('d/m/Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Batch Number',
            'Manufacturer',
            'Category',
            'Sub Category',
            'Unit',
            'Quantity',
            'Unit Price',
            'Agent Price',
            'Description',
            'Stock Date',
        ];
    }
}
