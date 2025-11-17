<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use App\Models\Product;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\Country;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStat extends BaseWidget
{
    protected function getStats(): array
    {
        // Get logged-in user
        $user = Filament::auth()->user();

        // Get currency symbol
        $symbol = Country::where('id', $user->country_id)->value('currency_symbol') ?? '₦';

        // Get the vendor_id of the user 
        $vendorId = $user?->vendor?->id;

        // Vendor-specific data
        $productCount = Product::where('vendor_id', $vendorId)->count();
        $orderCount = Order::where('vendor_id', $vendorId)->count();

        // ✅ Fetch total income where status = 'completed'
        $totalIncome = Order::where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->sum('total_amount');

        return [
            Stat::make('Products', $productCount)
                ->description('Total products in inventory')
                ->descriptionIcon('heroicon-o-cube')
                ->chart([10, 15, 5, 20, 30])
                ->icon('heroicon-s-cube')
                ->color('gray'),

            Stat::make('Orders', $orderCount)
                ->description('Total orders received')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->chart([5, 10, 15, 20, 25])
                ->icon('heroicon-s-shopping-cart')
                ->color('info'),

            //  Show Total Income
            Stat::make('Total Income', $symbol . number_format($totalIncome, 2))
                ->description('Total income from completed sales')
                ->descriptionIcon('heroicon-o-banknotes')
                ->icon('heroicon-s-banknotes')
                ->chart([5, 10, 10, 30, 60])
                ->color('success'),
        ];
    }
}
