<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\User;
use Auth;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function pending()
    {
        return view('orders.pending_orders');
    }

    public function accepted()
    {
        $user = User::where('id',Auth::id())->first();
        $orders = $user->vendor->orders()->where('status', 'accepted')->with(['product', 'agent.user'])->get();

        return view('orders.accepted_orders', compact('orders'));
    }
    public function supplied()
    {
        $user = User::where('id',Auth::id())->first();
        $orders = $user->vendor->orders()->where('status', 'supplied')->with(['product', 'agent.user'])->get();

        return view('orders.supplied_orders', compact('orders'));
    }
}
