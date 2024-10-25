<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function supplied(){

        $vendor = auth()->user()->vendor;

        if($vendor){
            $totalSuppliedOrders = $vendor->products()->withCount(['orders' => function ($query){
                $query->where('status', 'confirmed');
            }])->get()->sum('orders_count');

            return response()->json(['status' => true, 'message' => 'Total supplied order(s)', 'data' => ['total' => $totalSuppliedOrders]]);
        }

        return response()->json(['status' => false, 'message' => 'Vendor not found']);
    }

    public function pending(){

        $vendor = auth()->user()->vendor;

        if($vendor){
            $totalSuppliedOrders = $vendor->products()->withCount(['orders' => function ($query){
                $query->where('status', 'pending');
            }])->get()->sum('orders_count');

            return response()->json(['status' => true, 'message' => 'Total pending order(s)', 'data' => ['total' => $totalSuppliedOrders]]);
        }

        return response()->json(['status' => false, 'message' => 'Vendor not found']);
    }

    public function accepted(){

        $vendor = auth()->user()->vendor;

        if($vendor){
            $totalSuppliedOrders = $vendor->products()->withCount(['orders' => function ($query){
                $query->where('status', 'accepted');
            }])->get()->sum('orders_count');

            return response()->json(['status' => true, 'message' => 'Total accepted order(s)', 'data' => ['total' => $totalSuppliedOrders]]);
        }

        return response()->json(['status' => false, 'message' => 'Vendor not found']);
    }

    public function total(){

        $vendor = auth()->user()->vendor;

        if($vendor){
            $totalSuppliedOrders = $vendor->products()->withCount('orders')->get()->sum('orders_count');

            return response()->json(['status' => true, 'message' => 'Total accepted order(s)', 'data' => ['total' => $totalSuppliedOrders]]);
        }

        return response()->json(['status' => false, 'message' => 'Vendor not found']);
    }
}
