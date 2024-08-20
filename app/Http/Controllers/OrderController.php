<?php

namespace App\Http\Controllers;
use App\Models\Order;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function pending()
    {
        return view('orders.pending_orders');
    } 

    public function accepted()
    {
        $orders = Order::where('vendor_id', auth()->user()->vendor->id)
                        ->where('status', 'accepted')->get();
        return view('orders.accepted_orders', compact('orders'));
    } 
    public function rejected()
    {
        $orders = Order::where('vendor_id', auth()->user()->vendor->id)
                        ->where('status', 'declined')->get();
        return view('orders.rejected_orders', compact('orders'));
    } 
}
