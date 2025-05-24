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

            return Product::leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('sub_categories', 'products.sub_category_id', '=', 'sub_categories.id')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->where('vendor_id', $userId)
            ->select(
                'products.name',
                'products.batch_number',
                'products.manufacturer',
                'categories.name as category_name', 
                'sub_categories.name as subcategory_name',
                'units.name as unit_name',
                'products.quantity',
                'products.unit_price',
                'products.agent_price',
                'products.description',
                'products.stock_date'
            )
            ->get();
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
