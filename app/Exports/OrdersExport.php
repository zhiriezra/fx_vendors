<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrdersExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Order::join('products', 'orders.product_id', '=', 'products.id')
        ->join('vendors', 'products.vendor_id', '=', 'vendors.id')
        ->join('users as vendor_users', 'vendors.user_id', '=', 'vendor_users.id') // For Vendor's User Info
        ->join('farmers', 'orders.farmer_id', '=', 'farmers.id') // For Farmer's Info
        ->join('agents', 'orders.agent_id', '=', 'agents.id')
        ->join('users as agent_users', 'agents.user_id', '=', 'agent_users.id') // For Agent's User Info
        ->where('vendors.user_id', auth()->id()) // Filter by Auth user
        ->select(
            'products.name as product_name',
            DB::raw("CONCAT(farmers.fname, ' ', farmers.lname) as farmer_fullname"),
            DB::raw("CONCAT(agent_users.firstname, ' ', agent_users.lastname) as agent_fullname"),
            'orders.quantity',
            'orders.status'
        )
        ->get();
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Farmer Name',
            'Agent Name',
            'Quantity',
            'Status',
        ];
    }
}
