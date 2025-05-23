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
            ->join('transactions', 'orders.transaction_id', '=', 'transactions.id')
            ->join('vendors', 'products.vendor_id', '=', 'vendors.id')
            ->join('users as vendor_users', 'vendors.user_id', '=', 'vendor_users.id')
            ->join('farmers', 'orders.farmer_id', '=', 'farmers.id')
            ->join('agents', 'orders.agent_id', '=', 'agents.id')
            ->join('users as agent_users', 'agents.user_id', '=', 'agent_users.id')
            ->where('vendors.user_id', auth()->id())
            ->select(
                'transactions.uuid as transaction_uuid', // Include UUID from transactions
                'products.name as product_name',
                DB::raw("CONCAT(farmers.fname, ' ', farmers.lname) as farmer_fullname"),
                DB::raw("CONCAT(agent_users.firstname, ' ', agent_users.lastname) as agent_fullname"),
                'orders.quantity',
                'orders.unit_price',
                'orders.status',
                'orders.created_at',
                'orders.updated_at'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'Transaction UUID', 
            'Product Name',
            'Farmer Name',
            'Agent Name',
            'Quantity',
            'Unit Price',
            'Status',
            'Created At',
            'Updated At'
        ];
    }
}
