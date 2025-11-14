<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use App\Filament\Resources\OrderResource;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getHeaderActions(): array
    {
        return [
            Action::make('salesRecords')
                ->label('Sales Records')
                ->icon('heroicon-o-chart-bar')
                ->color('success')
                ->url(OrderResource::getUrl('sales'))
                ->outlined(),
        ];
    }
}

