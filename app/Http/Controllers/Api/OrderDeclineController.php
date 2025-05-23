<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Escrow;
use App\Models\Product;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Models\OrderProcessing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\GeneralWalletService;
use App\Services\PushNotificationService;

class OrderDeclineController extends Controller
{
    use ApiResponder;

    protected $GeneralWalletService;
    protected $pushNotificationService;

    public function __construct(
        GeneralWalletService $GeneralWalletService,
        PushNotificationService $pushNotificationService
    ) {
        $this->GeneralWalletService = $GeneralWalletService;
        $this->pushNotificationService = $pushNotificationService;
    }

    /**
     * Vendor declines an order placed by the agent.
     */
    public function orderDecline(int $escrow_id)
    {
        try {
            // Fetch and validate escrow
            $escrow = $this->findAndValidateEscrow($escrow_id);

            // Get related orders
            $ordersToDecline = $this->getOrdersForDecline($escrow);

            // Start DB transaction
            DB::beginTransaction();

            // Restore product quantities
            $this->restoreProductQuantities($ordersToDecline);

            // Refund wallet if paid via wallet
            $this->refundWalletToAgent($escrow);

            // Update escrow status
            $this->markEscrowAsDeclined($escrow);

            // Notify agent
            $this->notifyAgent($escrow);

            // Commit transaction
            DB::commit();

            return $this->success(null, "Order successfully declined.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order decline failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Fetch and validate escrow
     */
    private function findAndValidateEscrow(int $escrow_id): Escrow
    {
        $escrow = Escrow::with(['agent.user', 'vendor.user'])->find($escrow_id);

        if (!$escrow) {
            throw new \Exception("Order not found.");
        }

        if ($escrow->status !== 'pending') {
            throw new \Exception("Only pending orders can be declined.");
        }

        return $escrow;
    }

    /**
     * Get associated orders
     */
    private function getOrdersForDecline(Escrow $escrow): Collection
    {
        $orders = Order::where('escrow_id', $escrow->id)->get();

        if ($orders->isEmpty()) {
            throw new \Exception("No products found for this order.");
        }

        return $orders;
    }

    /**
     * Restore product quantities
     */
    private function restoreProductQuantities(Collection $ordersToDecline): void
    {
        foreach ($ordersToDecline as $order) {
            $product = Product::find($order->product_id);

            if ($product) {
                $product->quantity += $order->quantity;
                $product->save();
            }
        }
    }

    /**
     * Refund money to agent if payment type is wallet
     */
    private function refundWalletToAgent(Escrow $escrow): void
    {
        if ($escrow->payment_type === 'wallet') {

            $agent = $escrow->agent->user;

            if (!$agent) {
                throw new \Exception("Agent user not found.");
            }

            $defaultProvider = $this->GeneralWalletService->getDefaultWalletProviderForUser($agent);

            $meta = [
                'type' => 'transaction',
                'transaction_id' => $escrow->transaction_id,
                'description' => "Order declined - refund for " . $escrow->transaction_id
            ];

            $agent->walletDeposit($agent->id, $defaultProvider, $escrow->total, $meta);
        }
    }

    /**
     * Mark escrow as declined
     */
    private function markEscrowAsDeclined(Escrow $escrow): void
    {
        $escrow->status = 'declined';
        $escrow->save();

        OrderProcessing::create([
            'escrow_id' => $escrow->id,
            'stage' => 'declined'
        ]);
    }

    /**
     * Notify agent that order was declined
     */
    private function notifyAgent(Escrow $escrow): void
    {
        $agent = $escrow->agent->user;
        $vendor = $escrow->vendor->user;

        $title = 'Order Declined';
        $body = "Your order has been declined by {$vendor->firstname} {$vendor->lastname}.";
        $data = [
            'type' => 'single',
            'user_id' => $agent->id,
            'transaction_id' => $escrow->transaction_id
        ];

        $this->pushNotificationService->sendToUser($agent, $title, $body, $data);

        Log::info('Push notification sent to agent about declined order', [
            'agent_user_id' => $agent->id,
            'transaction_id' => $escrow->transaction_id
        ]);
    }
}
