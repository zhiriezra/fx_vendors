<?php

namespace App\Http\Controllers\Api;

use App\Models\Escrow;
use App\Traits\ApiResponder;
use App\Models\OrderProcessing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;

class OrderSupplyController extends Controller
{
    use ApiResponder;

    protected $pushNotificationService;

    public function __construct(PushNotificationService $pushNotificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
    }

    /**
     * Vendor confirms supply of all items in an escrow.
     */
    public function orderSupply(int $escrow_id)
    {
        try {
            // Fetch and validate escrow
            $escrow = $this->findAndValidateEscrow($escrow_id);

            // Ensure user is the vendor
            $this->ensureUserIsVendor($escrow);

            // Ensure escrow status is accepted
            $this->ensureEscrowIsAccepted($escrow);

            // Update escrow status to 'supplied'
            $this->markEscrowAsSupplied($escrow);

            // Log processing stage
            $this->logEscrowProcessingStage($escrow);

            // Notify agent
            $this->notifyAgent($escrow);

            return $this->success(null, 'Order supplied successfully.');

        } catch (\Exception $e) {
            Log::error('Order supply confirmation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    private function findAndValidateEscrow(int $escrow_id): Escrow
    {
        $escrow = Escrow::with(['vendor.user', 'agent.user'])->find($escrow_id);

        if (!$escrow) {
            throw new \Exception("Order not found.");
        }

        return $escrow;
    }

    private function ensureUserIsVendor(Escrow $escrow): void
    {
        $currentUser = auth()->user();

        if ($escrow->vendor->user_id !== $currentUser->id) {
            throw new \Exception("You are not authorized to confirm supply for this order.");
        }
    }

    private function ensureEscrowIsAccepted(Escrow $escrow): void
    {
        if ($escrow->status !== 'accepted') {
            throw new \Exception("You can only confirm supply for an accepted order.");
        }
    }

    private function markEscrowAsSupplied(Escrow $escrow): void
    {
        $escrow->status = 'supplied';
        $escrow->save();
    }

    private function logEscrowProcessingStage(Escrow $escrow): void
    {
        OrderProcessing::create([
            'escrow_id' => $escrow->id,
            'stage' => 'supplied',
        ]);
    }

    private function notifyAgent(Escrow $escrow): void
    {
        $agent = $escrow->agent->user;
        $vendor = $escrow->vendor->user;

        $title = 'Order Supplied';
        $body = "Your order has been supplied by {$vendor->firstname} {$vendor->lastname}.";
        $data = [
            'type' => 'single',
            'user_id' => $agent->id,
            'transaction_id' => $escrow->transaction_id
        ];

        $this->pushNotificationService->sendToUser($agent, $title, $body, $data);

        Log::info('Push notification sent to agent about supplied order', [
            'agent_user_id' => $agent->id,
            'transaction_id' => $escrow->transaction_id
        ]);
    }

}
