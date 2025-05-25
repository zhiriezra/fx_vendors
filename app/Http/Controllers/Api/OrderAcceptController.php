<?php

namespace App\Http\Controllers\Api;

use App\Models\Escrow;
use App\Traits\ApiResponder;
use App\Models\OrderProcessing;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;

class OrderAcceptController extends Controller
{
    use ApiResponder;

    protected $pushNotificationService;

    public function __construct(PushNotificationService $pushNotificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
    }

    /**
     * Vendor accepts an order (via escrow).
     */
    public function orderAccept(int $escrow_id)
    {
        try {
            // Fetch and validate escrow
            $escrow = $this->findAndValidateEscrow($escrow_id);

            // Ensure user is the vendor for all items
            $this->ensureUserIsVendorForAllItems($escrow);

            // Validate escrow status
            $this->ensureEscrowIsPending($escrow);

            // Mark escrow as accepted
            $this->markEscrowAsAccepted($escrow);

            // Log processing stage
            $this->logEscrowProcessingStage($escrow);

            // Notify agent
            $this->notifyAgent($escrow);

            return $this->success(null, 'Order accepted successfully.');

        } catch (\Exception $e) {
            Log::error('Order acceptance failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    // --- PRIVATE METHODS ---

    private function findAndValidateEscrow(int $escrow_id): Escrow
    {
        $escrow = Escrow::with(['vendor.user', 'agent.user'])->find($escrow_id);

        if (!$escrow) {
            throw new \Exception("Order not found.");
        }

        return $escrow;
    }

    private function ensureUserIsVendorForAllItems(Escrow $escrow): void
    {
        $currentUser = auth()->user();

        $allOrdersAreFromCurrentUser = $escrow->orders->every(function ($order) use ($currentUser) {
            return $order->product->vendor->user_id === $currentUser->id;
        });

        if (!$allOrdersAreFromCurrentUser) {
            throw new \Exception("You are not authorized to accept this order.");
        }
    }

    private function ensureEscrowIsPending(Escrow $escrow): void
    {
        if ($escrow->status !== 'pending') {
            throw new \Exception("Can only accept a pending order.");
        }
    }

    private function markEscrowAsAccepted(Escrow $escrow): void
    {
        $escrow->status = 'accepted';
        $escrow->save();
    }

    private function logEscrowProcessingStage(Escrow $escrow): void
    {
        OrderProcessing::create([
            'escrow_id' => $escrow->id,
            'stage' => 'accepted',
        ]);
    }

    private function notifyAgent(Escrow $escrow): void
    {
        $agent = $escrow->agent->user;
        $vendor = $escrow->vendor->user;

        $title = 'Order Accepted';
        $body = "Your order has been accepted by {$vendor->firstname} {$vendor->lastname}.";
        $data = [
            'type' => 'single',
            'user_id' => $agent->id,
            'transaction_id' => $escrow->transaction_id
        ];

        $this->pushNotificationService->sendToUser($agent, $title, $body, $data);

        Log::info('Push notification sent to agent about accepted order', [
            'agent_user_id' => $agent->id,
            'transaction_id' => $escrow->transaction_id
        ]);
    }

}
