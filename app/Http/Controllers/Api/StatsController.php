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

    //This should replace the above. One end point for all request should be better

    public function dashboardStats()
    {
        $vendor = auth()->user()->vendor;

        if ($vendor) {

            $products = $vendor->products()
                ->withCount([
                    'orders as confirmed_orders_count' => function ($query) {
                        $query->where('status', 'confirmed');
                    },
                    'orders as pending_orders_count' => function ($query) {
                        $query->where('status', 'pending');
                    },
                    'orders as accepted_orders_count' => function ($query) {
                        $query->where('status', 'accepted');
                    },
                    'orders'
                ])
                ->get();


            $totalConfirmedOrders = $products->sum('confirmed_orders_count');
            $totalPendingOrders = $products->sum('pending_orders_count');
            $totalAcceptedOrders = $products->sum('accepted_orders_count');
            $totalOrders = $products->sum('orders_count');

            return response()->json([
                'status' => true,
                'message' => 'Categories of Orders',
                'data' => [
                    'totalConfirmedOrders' => $totalConfirmedOrders,
                    'totalPendingOrders' => $totalPendingOrders,
                    'totalAcceptedOrders' => $totalAcceptedOrders,
                    'total_orders' => $totalOrders
                ]
            ]);
        }

        return response()->json(['status' => false, 'message' => 'Vendor not found']);
    }


}
