<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatsController extends Controller
{

    public function dashboardStats()
    {
        $vendor = auth()->user()->vendor;

        if ($vendor) {
            // Fetch products with order counts
            $products = $vendor->products()
                ->withCount([
                    'orders as confirmed_orders_count' => function ($query) {
                        $query->where('status', 'confirmed');
                    },
                    'orders as supplied_orders_count' => function ($query) {
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

            // Calculate total order counts
            $totalConfirmedOrders = $products->sum('confirmed_orders_count');
            $totalSuppliedOrders = $products->sum('supplied_orders_count');
            $totalPendingOrders = $products->sum('pending_orders_count');
            $totalAcceptedOrders = $products->sum('accepted_orders_count');
            $totalOrders = $products->sum('orders_count');
            
            // Calculate total earnings
            $totalEarnings = Order::where('status', 'completed')->whereHas('product', function ($query){
                $query->where('vendor_id', auth()->user()->vendor->id);
            })
            ->selectRaw('SUM(orders.quantity * agent_price) as total_earned')
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->value('total_earned');

            if($totalEarnings == NULL){
                $totalEarnings = "0";
            };

            // Calculate monthly earnings
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $monthlyEarnings  = Order::where('status', 'completed')->whereHas('product', function ($query){
                $query->where('vendor_id', auth()->user()->vendor->id);
            })
            ->whereYear('orders.created_at', $currentYear)
            ->whereMonth('orders.created_at', $currentMonth)
            ->selectRaw('SUM(orders.quantity * agent_price) as monthly_total_earned')
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->value('monthly_total_earned');

            if($monthlyEarnings  == NULL){
                $monthlyEarnings = "0";
            };

            // Count low stock and out of stock products
            $lowStockThreshold = 3; 
            $outOfStockCount = $vendor->products()->where('quantity', 0)->count();
            $lowStockCount = $vendor->products()->where('quantity', '>', 0)
                ->where('quantity', '<=', $lowStockThreshold)
                ->count();

            // Return the response
            return response()->json([
                'status' => true,
                'message' => 'Vendor statistics',
                'data' => [
                    'total_confirmed_orders' => $totalConfirmedOrders,
                    'total_supplied_orders' => $totalSuppliedOrders,
                    'total_pending_orders' => $totalPendingOrders,
                    'total_accepted_orders' => $totalAcceptedOrders,
                    'total_orders' => $totalOrders,
                    'total_earnings' => $totalEarnings,
                    'monthly_earnings' => $monthlyEarnings,
                    'total_products' => auth()->user()->vendor->products->count(),
                    'out_of_stock_products' => $outOfStockCount,
                    'low_stock_products' => $lowStockCount,
                    ]
            ], 200);
        }

        return response()->json(['status' => false, 'message' => 'Vendor not found'], 404);
    }

}
