<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\BulkAction;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;

class SalesRecords extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = OrderResource::class;
    protected static string $view = 'filament.resources.order-resource.pages.sales-records';


    protected static ?string $navigationLabel = 'Sales Records';

    protected static ?string $title = 'Sales Records';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->where('vendor_id', auth()->user()->vendor->id)
                    ->where('status', 'completed')
                    ->with(['agent.user', 'farmer', 'orderItems.product.manufacturer_product'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Transaction ID copied')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('agent.user.firstname')
                    ->label('Agent Name')
                    ->formatStateUsing(fn ($record) => $record->agent?->user?->firstname . ' ' . $record->agent?->user?->lastname)
                    ->searchable(['agent.user.firstname', 'agent.user.lastname']),
                Tables\Columns\TextColumn::make('agent.user.phone')
                    ->label('Agent Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money(fn () => auth()->user()->country?->currency ?? 'KES'),
                Tables\Columns\TextColumn::make('payment_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wallet' => 'success',
                        'cash' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sale Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_type')
                    ->options([
                        'wallet' => 'Wallet',
                        'cash' => 'Cash',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from'),
                        \Filament\Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->bulkActions([
                BulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Collection $records) {
                        $fileName = 'sales_records_' . date('Y-m-d_H-i-s') . '.xlsx';
                        return Excel::download(new OrdersExport, $fileName);
                    }),
            ]);
    }

    public function getSalesSummary(): array
    {
        $salesRecords = Order::where('vendor_id', auth()->user()->vendor->id)
            ->where('status', 'completed')
            ->get();

        $walletTotal = $salesRecords->where('payment_type', 'wallet')->sum('total_amount');
        $cashTotal = $salesRecords->where('payment_type', 'cash')->sum('total_amount');
        $grandTotal = $walletTotal + $cashTotal;

        return [
            'wallet_total' => $walletTotal,
            'cash_total' => $cashTotal,
            'grand_total' => $grandTotal,
            'total_orders' => $salesRecords->count(),
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportAll')
                ->label('Export All Sales')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $fileName = 'sales_records_' . date('Y-m-d_H-i-s') . '.xlsx';
                    return Excel::download(new OrdersExport, $fileName);
                }),
        ];
    }
}
