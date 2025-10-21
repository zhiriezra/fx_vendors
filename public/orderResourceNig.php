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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('vendor_id', auth()->user()->vendor->id)
            ->with(['agent.user', 'farmer.user', 'vendor.user', 'orderItems.product.manufacturer_product']);
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
                Tables\Columns\TextColumn::make('farmer.user.firstname')
                    ->label('Farmer Name')
                    ->formatStateUsing(fn ($record) => $record->farmer?->user?->firstname . ' ' . $record->farmer?->user?->lastname)
                    ->searchable(['farmer.user.firstname', 'farmer.user.lastname'])
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wallet' => 'success',
                        'cash' => 'warning',
                        default => 'gray',
                    }),
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
                    ->money('NGN')
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
                    ]),
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
                Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('New Status')
                            ->options(function ($record) {
                                $validTransitions = [
                                    'pending' => ['accepted', 'declined'],
                                    'accepted' => ['supplied'],
                                    'supplied' => [],
                                    'declined' => []
                                ];
                                
                                $currentStatus = strtolower($record->status);
                                $options = [];
                                
                                if (isset($validTransitions[$currentStatus])) {
                                    foreach ($validTransitions[$currentStatus] as $status) {
                                        $options[$status] = ucfirst($status);
                                    }
                                }
                                
                                return $options;
                            })
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (array $data, Order $record): void {
                        $validTransitions = [
                            'pending' => ['accepted', 'declined'],
                            'accepted' => ['supplied'],
                            'supplied' => [],
                            'declined' => []
                        ];

                        $currentStatus = strtolower($record->status);
                        $newStatus = strtolower($data['status']);

                        if (!isset($validTransitions[$currentStatus])) {
                            Notification::make()
                                ->title('Invalid Status')
                                ->body('Invalid current order status.')
                                ->danger()
                                ->send();
                            return;
                        }

                        if (!in_array($newStatus, $validTransitions[$currentStatus])) {
                            Notification::make()
                                ->title('Invalid Transition')
                                ->body("Invalid status transition from {$currentStatus} to {$newStatus}.")
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            DB::beginTransaction();

                            // Update order status
                            $record->status = $newStatus;
                            $record->save();

                            // Handle status-specific logic
                            if ($newStatus === 'accepted') {
                                // Send push notification to agent
                                if ($record->agent && $record->agent->user) {
                                    $pushNotificationService = app(PushNotificationService::class);
                                    $title = 'Order Accepted';
                                    $body = 'Your order has been accepted by the vendor.';
                                    $data = [
                                        'type' => 'order',
                                        'order_id' => $record->id,
                                        'transaction_id' => $record->transaction_id
                                    ];

                                    $pushNotificationService->sendToUser($record->agent->user, $title, $body, $data);
                                }
                            }

                            if ($newStatus === 'declined') {
                                // Restore product quantities
                                foreach ($record->orderItems as $orderItem) {
                                    $product = $orderItem->product;
                                    $product->quantity += $orderItem->quantity;
                                    $product->save();
                                }

                                // Handle escrow refund if payment type is wallet
                                if ($record->escrow && $record->payment_type === 'wallet') {
                                    $agent = $record->agent->user;
                                    $defaultProvider = app(GeneralWalletService::class)->getDefaultWalletProviderForUser($agent);

                                    $meta = [
                                        'type' => 'refund',
                                        'transaction_id' => $record->transaction_id,
                                        'description' => "Order declined - refund for " . $record->transaction_id
                                    ];

                                    $agent->walletDeposit($agent->id, $defaultProvider, $record->total_amount, $meta);
                                }

                                // Mark escrow as cancelled
                                if ($record->escrow) {
                                    $record->escrow->status = 'cancelled';
                                    $record->escrow->save();
                                }

                                // Send push notification to agent
                                $pushNotificationService = app(PushNotificationService::class);
                                $title = 'Order Declined';
                                $body = "Your order #{$record->id} has been declined by the vendor.";
                                $data = [
                                    'type' => 'order',
                                    'order_id' => $record->id
                                ];

                                $pushNotificationService->sendToUser($record->agent->user, $title, $body, $data);
                            }

                            if ($newStatus === 'supplied') {
                                // Send push notification to agent
                                if ($record->agent && $record->agent->user) {
                                    $pushNotificationService = app(PushNotificationService::class);
                                    $title = 'Order Supplied';
                                    $body = "Your order #{$record->id} has been supplied by the vendor.";
                                    $data = [
                                        'type' => 'order',
                                        'order_id' => $record->id,
                                        'transaction_id' => $record->transaction_id
                                    ];

                                    $pushNotificationService->sendToUser($record->agent->user, $title, $body, $data);
                                }
                            }

                            DB::commit();

                            Notification::make()
                                ->title('Status Updated')
                                ->body("Order has been {$newStatus} successfully")
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Order status update failed', [
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);

                            Notification::make()
                                ->title('Update Failed')
                                ->body("Failed to update order status: " . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => in_array(strtolower($record->status), ['pending', 'accepted'])),
                Action::make('viewDetails')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    // ->url(fn (Order $record): string => static::getResource()::getUrl('view', ['record' => $record]))
                    ->url(fn ($record) => OrderResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
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
                Action::make('exportAll')
                    ->label('Export All Orders')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $fileName = 'orders_' . date('Y-m-d_H-i-s') . '.xlsx';
                        return Excel::download(new OrdersExport, $fileName);
                    }),
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
                                TextEntry::make('transaction_id')
                                    ->label('Transaction ID')
                                    ->copyable()
                                    ->copyMessage('Transaction ID copied'),
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
                                    ->money('NGN'),
                                TextEntry::make('commission')
                                    ->label('Commission')
                                    ->money('NGN'),
                                TextEntry::make('service_charge')
                                    ->label('Service Charge')
                                    ->money('NGN'),
                                TextEntry::make('payment_type')
                                    ->label('Payment Type')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'wallet' => 'success',
                                        'cash' => 'warning',
                                        default => 'gray',
                                    }),
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
                                    ->dateTime(),
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
                                TextEntry::make('farmer.user.firstname')
                                    ->label('Farmer Name')
                                    ->formatStateUsing(fn ($record) => $record->farmer?->user?->firstname . ' ' . $record->farmer?->user?->lastname),
                                TextEntry::make('farmer.user.phone')
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
                                            ->label('Quantity'),
                                        TextEntry::make('unit_price')
                                            ->label('Unit Price')
                                            ->money('NGN'),
                                        TextEntry::make('agent_price')
                                            ->label('Agent Price')
                                            ->money('NGN'),
                                        TextEntry::make('commission')
                                            ->label('Commission')
                                            ->money('NGN'),
                                        TextEntry::make('total')
                                            ->label('Total')
                                            ->formatStateUsing(fn ($record) => '₦' . number_format($record->quantity * $record->unit_price, 2)),
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
