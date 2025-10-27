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

   // protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {

          // Get logged-in user
        $user = Filament::auth()->user();
     
        $symbol = Country::where('id', $user->country_id)->value('currency_symbol') ?? '₦';


         $totalCommission = 15000;


        // Get the vendor_id of the user
        //$vendorId = $user->vendor_id;
 
        $vendorId = $user?->vendor?->id;

        // Get vendor-specific data
        $productCount = Product::where('vendor_id', $vendorId)->count();
        $orderCount = Order::where('vendor_id', $vendorId)->count();
        $totalCommission = Order::where('vendor_id', $vendorId)->sum('commission');

        return [
            Stat::make('Products', $productCount)
                ->description('Total products in inventory')
                ->descriptionIcon('heroicon-o-cube')
                ->chart([10, 15, 5, 20, 30])
                ->icon('heroicon-s-cube')                
                ->color('gray'),

            Stat::make('Orders', $orderCount)
                ->description('Total orders recieved')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->chart([5, 10, 15, 20, 25])
                ->icon('heroicon-s-shopping-cart')
                ->color('info'),          
            
            Stat::make('Total Income', $symbol . number_format($totalCommission, 2))
                ->description('Total Income from Sales')
                ->descriptionIcon('heroicon-o-banknotes')
                ->icon('heroicon-s-banknotes')
                ->chart([5, 10, 10, 30, 60])
                ->color('success'),
            ];
    }

   
}
