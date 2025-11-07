<?php

namespace App\Filament\Widgets;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use App\Models\Order;
use Carbon\Carbon;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Sales Overview';

    protected function getData(): array
    {
        $user = Filament::auth()->user();
        $vendorId = $user?->vendor?->id;

        $data = Order::selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->whereYear('created_at', Carbon::now()->year)
            ->where('vendor_id', $vendorId)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => array_values($data),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'borderColor' => '#9BD0F5',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => array_map(fn($m) => date('F', mktime(0, 0, 0, $m, 10)), array_keys($data)),
        ];
    }

    protected function getType(): string
    {
        return 'line'; 
    }
}
