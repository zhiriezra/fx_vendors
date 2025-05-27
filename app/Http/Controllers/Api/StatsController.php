<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\ApiResponder;
use App\Models\Product;
use App\Models\StockTracker;
class StatsController extends Controller
{
    use ApiResponder;

    public function orderStats()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor not found', 404);
        }

        // Get all orders for the vendor
        $allOrders = Order::where('vendor_id', $vendor->id)->get(); 

        // Get orders by status
        $completedOrders = (clone $allOrders)->where('status', 'completed')->count();
        $pendingOrders = (clone $allOrders)->where('status', 'pending')->count();
        $acceptedOrders = (clone $allOrders)->where('status', 'accepted')->count();
        $suppliedOrders = (clone $allOrders)->where('status', 'supplied')->count();
        $cancelledOrders = (clone $allOrders)->where('status', 'cancelled')->count();

        $totalOrders = $allOrders->count();

        // Calculate earnings
        $totalEarnings = Order::where('status', 'completed')->where('vendor_id', $vendor->id)->sum('total_amount');

        // Calculate monthly earnings
        $monthlyEarnings = Order::where('status', 'completed')->where('vendor_id', $vendor->id)->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->sum('total_amount');

        // Calculate weekly earnings
        $weeklyEarnings = Order::where('status', 'completed')->where('vendor_id', $vendor->id)->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->sum('total_amount');

        $stockTracker = StockTracker::first();
        // Get low stock count
        $lowStockCount = Product::where('vendor_id', $vendor->id)
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', $stockTracker->quantity) // Assuming 3 is the low stock threshold
            ->count();

        // Get out of stock count
        $outOfStockCount = Product::where('vendor_id', $vendor->id)
            ->where('quantity', 0)
            ->count();

        return $this->success([
            'orders' => [
                'total' => $totalOrders,
                'completed' => $completedOrders,
                'pending' => $pendingOrders,
                'accepted' => $acceptedOrders,
                'supplied' => $suppliedOrders,
                'cancelled' => $cancelledOrders
            ],
            'earnings' => [
                'total' => $totalEarnings,
                'monthly' => $monthlyEarnings,
                'weekly' => $weeklyEarnings
            ],
            'out_of_stock_count' => $outOfStockCount,
            'low_stock_count' => $lowStockCount
        ], 'Statistics retrieved successfully');
    }
}
