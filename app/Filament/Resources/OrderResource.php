<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Filament\Resources\OrderResource;
use App\Exports\OrdersExport;
use App\Services\GeneralWalletService;
use App\Services\PushNotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('vendor_id', auth()->user()->vendor->id)
            ->with(['agent.user', 'farmer', 'vendor.user', 'orderItems.product.manufacturer_product']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agent.user.firstname')
                    ->label('Agent Name')
                    ->formatStateUsing(fn ($record) => $record->agent?->user?->firstname . ' ' . $record->agent?->user?->lastname)
                    ->searchable(['agent.user.firstname', 'agent.user.lastname']),
                Tables\Columns\TextColumn::make('agent.user.phone')
                    ->label('Agent Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('farmer.fname')
                    ->label('Farmer Name')
                    ->formatStateUsing(fn ($record) => $record->farmer?->fname . ' ' . $record->farmer?->lname)
                    ->searchable(['farmer.fname', 'farmer.lname'])
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money(fn () => auth()->user()->country?->currency ?? 'KES')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wallet' => 'success',
                        'cash' => 'warning',
                        default => 'gray',
                    })
                    ->visible(fn () => auth()->user()->country_id == 1),
                Tables\Columns\TextColumn::make('delivery_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pickup' => 'info',
                        'delivery' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'info',
                        'supplied' => 'success',
                        'declined' => 'danger',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('orderItems')
                    ->label('Items')
                    ->formatStateUsing(fn ($record) => $record->orderItems->count() . ' items')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('service_charge')
                    ->money(fn () => auth()->user()->country?->currency ?? 'KES')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'supplied' => 'Supplied',
                        'declined' => 'Declined',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('payment_type')
                    ->options([
                        'wallet' => 'Wallet',
                        'cash' => 'Cash',
                    ])
                    ->visible(fn () => auth()->user()->country_id == 1),
                Tables\Filters\SelectFilter::make('delivery_type')
                    ->options([
                        'pickup' => 'Pickup',
                        'delivery' => 'Delivery',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
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
            ->actions([
                Action::make('viewDetails')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => OrderResource::getUrl('view', ['record' => $record])),
            ])
            
            ->bulkActions([
                BulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Collection $records) {
                        $fileName = 'orders_' . date('Y-m-d_H-i-s') . '.xlsx';
                        return Excel::download(new OrdersExport, $fileName);
                    }),
            ])
            ->headerActions([
                // Action::make('exportAll')
                //     ->label('Export')
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->action(function () {
                //         $fileName = 'orders_' . date('Y-m-d_H-i-s') . '.xlsx';
                //         return Excel::download(new OrdersExport, $fileName);
                //     }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Order Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'accepted' => 'info',
                                        'supplied' => 'success',
                                        'declined' => 'danger',
                                        'completed' => 'success',
                                        default => 'gray',
                                    }),
                                TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->money(fn () => auth()->user()->country?->currency ?? 'KES'),
                                TextEntry::make('payment_type')
                                    ->label('Payment Type')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'wallet' => 'success',
                                        'cash' => 'warning',
                                        default => 'gray',
                                    })
                                    ->visible(fn () => auth()->user()->country_id == 1),
                                TextEntry::make('delivery_type')
                                    ->label('Delivery Type')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pickup' => 'info',
                                        'delivery' => 'success',
                                        default => 'gray',
                                    }),
                                TextEntry::make('created_at')
                                    ->label('Order Date')
                                    ->date(),
                            ]),
                    ]),

                Section::make('Agent Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('agent.user.firstname')
                                    ->label('Agent Name')
                                    ->formatStateUsing(fn ($record) => $record->agent?->user?->firstname . ' ' . $record->agent?->user?->lastname),
                                TextEntry::make('agent.user.phone')
                                    ->label('Agent Phone'),
                                TextEntry::make('agent.current_location')
                                    ->label('Delivery Address'),
                            ]),
                    ]),

                Section::make('Farmer Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('farmer.fname')
                                    ->label('Farmer Name')
                                    ->formatStateUsing(fn ($record) => $record->farmer?->fname . ' ' . $record->farmer?->lname),
                                TextEntry::make('farmer.mobile_no')
                                    ->label('Farmer Phone'),
                            ]),
                    ]),

                Section::make('Order Items')
                    ->schema([
                        RepeatableEntry::make('orderItems')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('product.manufacturer_product.name')
                                            ->label('Product Name'),
                                        TextEntry::make('quantity')
                                            ->label('QTY'),
                                        TextEntry::make('product.batch_number')
                                            ->label('Batch Number'),
                                        TextEntry::make('unit_price')
                                            ->label('Unit Price')
                                            ->money(fn () => auth()->user()->country?->currency ?? 'KES'),
                                        TextEntry::make('agent_price')
                                            ->label('Agent Price')
                                            ->money(fn () => auth()->user()->country?->currency ?? 'KES'),
                                        TextEntry::make('subtotal')
                                            ->label('Total')
                                            ->money(fn () => auth()->user()->country?->currency ?? 'KES'),
                                    ]),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'sales' => Pages\SalesRecords::route('/sales'),
        ];
    }

    public static function canCreate(): bool
    {
        // Prevent creating new transactions
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        // Prevent editing transactions
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        // Prevent deleting transactions
        return false;
    }
}
