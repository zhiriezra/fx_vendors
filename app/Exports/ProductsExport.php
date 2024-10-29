<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $userId = Auth::user()->vendor->id;

            return Product::leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('sub_categories', 'products.sub_category_id', '=', 'sub_categories.id')
            ->where('vendor_id', $userId)
            ->select(
                'products.name',
                'products.batch_number',
                'products.type',
                'products.manufacturer',
                'categories.name as category_name', 
                'sub_categories.name as subcategory_name',
                'products.quantity',
                'products.unit_price',
                'products.agent_price',
                'products.description',
                'products.stock_date'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Batch Number',
            'Type',
            'Manufacturer',
            'Category',
            'Sub Category',
            'Quantity',
            'Unit Price',
            'Agent Price',
            'Description',
            'Stock Date',
        ];
    }
}
