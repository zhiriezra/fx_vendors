<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Escrow;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\GeneralWalletService;
use App\Services\PushNotificationService;


class OrderController extends Controller
{
    use ApiResponder;

    protected $pushNotificationService;

    public function __construct(PushNotificationService $pushNotificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         $vendorId = auth()->user()->vendor->id;

        $orders = Order::where('vendor_id', $vendorId)->get();

        if($orders->isEmpty()){
            return $this->error(null, "No orders found!", 404);
        }

        return $this->success(['orders' => $orders->map(fn($e) => $this->formatOrder($e))], 'All orders');

    }

    public function singleOrder($order_id)
    {
        $vendorId = auth()->user()->vendor->id;

        $order = Order::where('id', $order_id)
            ->where('vendor_id', $vendorId)
            ->with(['product.vendor.user', 'agent.user', 'vendor.user'])
            ->first();

        if (!$order) {
            return $this->error(null, "No order found.", 404);
        }

        return $this->success(['order' => $this->formatOrder($order)], 'Single Order');
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $validTransitions = [
            'pending' => ['accepted', 'declined'],
            'accepted' => ['supplied'],
            'supplied' => [],
            'declined' => []
        ];

        $request->validate([
            'status' => 'required|string|in:accepted,declined,supplied'
        ]);

        $vendorId = auth()->user()->vendor->id;

        $order = Order::where('id', $id)
            ->whereHas('orderItems.product', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })
            ->with(['orderItems.product', 'agent.user'])
            ->first();

        if (!$order) {
            return $this->error(null, "Order not found or you don't have permission to update this order.", 404);
        }

        $currentStatus = strtolower($order->status);
        $newStatus = strtolower($request->status);

        if (!isset($validTransitions[$currentStatus])) {
            return $this->error(null, "Invalid current order status.", 400);
        }

        if (!in_array($newStatus, $validTransitions[$currentStatus])) {
            return $this->error(null, "Invalid status transition from {$currentStatus} to {$newStatus}.", 400);
        }

        try {
            DB::beginTransaction();

            // Update order status
            $order->status = $newStatus;
            $order->save();

            // Handle supplied status
            if ($newStatus === 'accepted') {
                // Send push notification to agent
                if ($order->agent && $order->agent->user) {
                    $title = 'Order Accepted';
                    $body = 'Your order has been accepted by the vendor.';
                    $data = [
                        'type' => 'order',
                        'order_id' => $order->id,
                        'transaction_id' => $order->transaction_id
                    ];

                    $this->pushNotificationService->sendToUser($order->agent->user, $title, $body, $data);
                }
            }

            // Handle declined status
            if ($newStatus === 'declined') {
                // Restore product quantities


                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    $product->quantity += $orderItem->quantity;
                    $product->save();
                }

                // Handle escrow refund if payment type is wallet
                if ($order->escrow && $order->payment_type === 'wallet') {
                    $agent = $order->agent->user;
                    $defaultProvider = app(GeneralWalletService::class)->getDefaultWalletProviderForUser($agent);

                    $meta = [
                        'type' => 'refund',
                        'transaction_id' => $order->transaction_id,
                        'description' => "Order declined - refund for " . $order->transaction_id
                    ];

                    $agent->walletDeposit($agent->id, $defaultProvider, $order->total_amount, $meta);
                }

                // Mark escrow as cancelled
                if ($order->escrow) {
                    $order->escrow->status = 'cancelled';
                    $order->escrow->save();
                }

                // Send push notification to agent
                    $title = 'Order Declined';
                    $body = "Your order #{$order->id} has been declined by the vendor.";
                    $data = [
                        'type' => 'order',
                        'order_id' => $order->id
                    ];

                    $this->pushNotificationService->sendToUser($order->agent->user, $title, $body, $data);
            }

            // Handle supplied status
            if ($newStatus === 'supplied') {
                // Send push notification to agent
                if ($order->agent && $order->agent->user) {
                    $title = 'Order Supplied';
                    $body = "Your order #{$order->id} has been supplied by the vendor.";
                    $data = [
                        'type' => 'order',
                        'order_id' => $order->id,
                        'transaction_id' => $order->transaction_id
                    ];

                    $this->pushNotificationService->sendToUser($order->agent->user, $title, $body, $data);
                }
            }


            DB::commit();
            return $this->success(['order' => $this->formatOrder($order)], "Order has been {$newStatus} successfully");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order status update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error(null, "Failed to update order status: " . $e->getMessage(), 500);
        }
    }


    //Export user Orders
    public function exportOrders()
    {
        // Check if the vendor has any orders
        $hasOrders = Order::join('products', 'orders.product_id', '=', 'products.id')
            ->join('vendors', 'products.vendor_id', '=', 'vendors.id')
            ->where('vendors.user_id', auth()->id())
            ->exists();

        if (!$hasOrders) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found for export.'
            ], 404);
        }

        // Proceed with export if orders exist
        $fileName = 'orders_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new OrdersExport, $fileName);
    }

    /**
     * Checks if any order in the escrow belongs to the given vendor.
     */
    private function hasOrderForVendor(Escrow $escrow, int $vendorId): bool
    {
        return $escrow->orders->contains(function ($order) use ($vendorId) {
            return $order->product?->vendor_id === $vendorId;
        });
    }


    public function salesRecord()
    {
        // Fetch completed orders for the current vendor
        $salesRecords = Order::where('vendor_id', auth()->user()->vendor->id)
            ->where('status', 'completed')
            ->get();

        if ($salesRecords->isEmpty()) {
            return $this->error(null, 'No completed sales found.', 404);
        }

        // Calculate totals
        $walletTotal = $salesRecords->where("payment_type", "wallet")->sum('total_amount');
        $cashTotal = $salesRecords->where("payment_type", "cash")->sum('total_amount');

        // Format order records
        $formattedSales = $salesRecords->map(function ($record) {
            return [
                'id' => $record->id,
                'transaction_id' => $record->transaction_id,
                'amount' => (float) $record->total_amount,
                'payment_type' => $record->payment_type,
                'created_at_' => $record->created_at->diffForHumans()
            ];
        });

        return $this->success([
            'wallet_total' => (float) $walletTotal,
            'cash_total' => (float) $cashTotal,
            'grand_total' => (float) ($walletTotal + $cashTotal),
            'orders' => $formattedSales
        ], 'Sales records retrieved successfully.');
    }

    public function salesDetail($order_id)
    {
        // Fetch the order with related items and agent
        $order = Order::with(['orderItems.product.product_images', 'agent.user'])
            ->where('id', $order_id)
            ->where('status', 'completed')
            ->first();

        if (!$order) {
            return $this->error(null, 'Sales record not found.', 404);
        }

        // Ensure current vendor owns this order
        if ($order->vendor_id !== auth()->user()->vendor->id) {
            return $this->error(null, 'You are not authorized to view this sales detail.', 403);
        }

        // Format each product from orderItems
        $products = $order->orderItems->map(function ($item) {
            $product = $item->product;
            return [
                'product_name' => $product->manufacturer_product->name,
                'first_image' => $product->manufacturer_product->image ?? env('APP_URL').'/default.png',
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->quantity * $item->unit_price,
                'commission' => (float) $item->commission,
            ];
        });

        // Format main order data
        $formattedOrder = [
            'id' => $order->id,
            'transaction_id' => $order->transaction_id,
            'transaction_total' => (float) $order->total_amount,
            'commission' => (float) $order->commission,
            'service_charge' => (float) $order->service_charge,
            'payment_type' => $order->payment_type,
            'created_at_human' => $order->created_at->diffForHumans(),
            'agent' => $order->agent->user->firstname . ' ' . $order->agent->user->lastname,
            'agent_phone' => $order->agent->user->phone,
            'products' => $products,
        ];

        //return
        return $this->success([
            'order' => $formattedOrder
        ], 'Sales detail retrieved successfully.');
    }


    /**
     * Formats a single escrow for API response.
     */
    private function formatOrder($order)
    {
        return [
            'id' => $order->id,
            'transaction_id' => $order->transaction_id,
            'total_amount' => (float) $order->total_amount,
            'payment_type' => $order->payment_type,
            'delivery_type' => $order->delivery_type,
            'commission' => (float) ($order->commission + ($order->exaf_commission ?? 0)),
            'agent' => $order->agent->user->firstname . ' ' . $order->agent->user->lastname,
            'agent_phone' => $order->agent->user->phone,
            'delivery_address' => $order->agent->current_location,
            'item_count' => $order->orderItems->count(),
            'product_count' => $order->orderItems->sum('quantity'),
            'created_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
            'updated_date' => Carbon::parse($order->updated_at)->format('M j, Y, g:ia'),
            'status' => $order->status,
            'products' => $order->orderItems->map(function ($order) {
                $total = (float) $order->quantity * $order->unit_price;
                return [
                    'id' => $order->product->id,
                    'product_name' => $order->product->manufacturer_product->name,
                    'first_image' => $order->product->manufacturer_product->image ?? env('APP_URL').'/default.png',
                    'quantity' => $order->quantity,
                    'unit_price' => (float) $order->unit_price,
                    'agent_price' => (float) $order->agent_price,
                    'commission' => (float) $order->commission,
                    'total' => (float) $total,
                ];
            }),
        ];
    }

}
