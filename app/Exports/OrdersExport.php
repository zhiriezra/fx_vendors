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
        return Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('manufacturer_products', 'products.manufacturer_product_id', '=', 'manufacturer_products.id')
            ->join('transactions', 'orders.transaction_id', '=', 'transactions.id')
            ->join('vendors', 'products.vendor_id', '=', 'vendors.id')
            ->join('users as vendor_users', 'vendors.user_id', '=', 'vendor_users.id')
            ->join('farmers', 'orders.farmer_id', '=', 'farmers.id')
            ->join('agents', 'orders.agent_id', '=', 'agents.id')
            ->join('users as agent_users', 'agents.user_id', '=', 'agent_users.id')
            ->where('vendors.user_id', auth()->id())
            ->select(
                'transactions.uuid as transaction_uuid',
                'orders.id as order_id',
                'manufacturer_products.name as product_name',
                'order_items.quantity',
                'order_items.subtotal',
                'orders.total_amount',
                'orders.payment_type',
                'orders.delivery_type',
                DB::raw("CONCAT(farmers.fname, ' ', farmers.lname) as farmer_fullname"),
                DB::raw("CONCAT(agent_users.firstname, ' ', agent_users.lastname) as agent_fullname"),
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
            'Order ID',
            'Product Name',
            'Item Quantity',
            'Item Subtotal',
            'Order Total',
            'Payment Type',
            'Delivery Type',
            'Farmer Name',
            'Agent Name',
            'Status',
            'Created At',
            'Updated At'
        ];
    }
}
