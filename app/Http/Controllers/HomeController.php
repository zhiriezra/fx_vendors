<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        
        $user = User::where('id',Auth::id())->first();
        if(Auth::user()->profile_completed == 0)
        return redirect()->route('vendor.profile.create')->with('message', 'Update your profile!');
        else
        $products = Auth::user()->vendor ? Product::where('vendor_id', Auth::user()->vendor->id)->get() : collect();
        $orders = Order::where('vendor_id', auth()->user()->vendor->id)->get();
        $pend_orders = Order::where('vendor_id', auth()->user()->vendor->id)->where('status', 'pending')->get();
        return view('dashboard', compact('user', 'products', 'orders', 'pend_orders'));
    }
}
