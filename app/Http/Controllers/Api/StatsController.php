<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class StatsController extends Controller
{

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
                        $query->where('status', 'supplied');
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
            $totalSuppliedOrders = $products->sum('confirmed_orders_count');
            $totalPendingOrders = $products->sum('pending_orders_count');
            $totalAcceptedOrders = $products->sum('accepted_orders_count');
            $totalOrders = $products->sum('orders_count');

            return response()->json([
                'status' => true,
                'message' => 'Categories of Orders',
                'data' => [
                    'total_confirmed_orders' => $totalConfirmedOrders,
                    'total_supplied_orders' => $totalSuppliedOrders,
                    'total_pending_orders' => $totalPendingOrders,
                    'total_accepted_orders' => $totalAcceptedOrders,
                    'total_orders' => $totalOrders
                ]
            ], 200);
        }

        return response()->json(['status' => false, 'message' => 'Vendor not found'], 404);
    }


}
