<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\Action::make('salesRecords')
    //             ->label('Sales Records')
    //             ->icon('heroicon-o-chart-bar')
    //             ->url(fn (): string => static::getResource()::getUrl('sales'))
    //             ->color('success'),
    //     ];
    // }
}
