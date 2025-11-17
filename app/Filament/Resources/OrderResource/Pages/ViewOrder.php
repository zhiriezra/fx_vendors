<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Services\GeneralWalletService;
use App\Services\PushNotificationService;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-s-pencil-square')
                ->form([
                    Forms\Components\Select::make('status')
                        ->label('New Status')
                        ->options(function () {
                            $record = $this->getRecord();
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
                ->action(function (array $data): void {
                    $record = $this->getRecord();
                    
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
                ->visible(fn () => in_array(strtolower($this->getRecord()->status), ['pending', 'accepted'])),
        ];
    }
}
