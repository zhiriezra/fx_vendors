<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use App\Models\OrderProcessing;
use App\Services\EscrowService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\GeneralWalletService;
use App\Services\PushNotificationService;

class OrderController extends Controller
{
    use ApiResponder;
    use PushNotificationService;

    public $total_amount = 0.0;

    protected $GeneralWalletService;

    public function __construct(GeneralWalletService $GeneralWalletService)
    {
        $this->pushNotificationService = new PushNotificationService();
        $this->GeneralWalletService = $GeneralWalletService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        $orders = $user->vendor->orders()->with(['product.product_images', 'agent.user'])
            ->get()
            ->map(fn($order) => $this->formatOrder($order));

        if ($orders->isEmpty()) {
            return $this->error(null, "No orders found!", 404);
        }

        return $this->success(['orders' => $orders], 'All orders');

    }


    public function pendingOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $orders = Order::where('status', 'pending')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get()->map(fn($order) => $this->formatOrder($order));


        if ($orders->isEmpty()) {
            return $this->error(null, "No pending orders found!", 404);
        }

        return $this->success(['orders' => $orders], 'All pending orders');
    }

    public function singleOrder($order_id)
    {
        $order = Order::with(['product.product_images', 'agent.user', 'farmer'])->find($order_id);

        if ($order) {
           return $this->success(['order' => $this->formatOrder($order)], 'Single Order');
        }

        return $this->error(['order' => $this->formatOrder($order)], 'No order found.', 404);
    }

    public function accept($order_id)
    {
        $order = Order::with('product')->find($order_id);

        if($order){

            if($order->status == 'pending'){

                $order->status = 'accepted';
                $order->save();

                OrderProcessing::create([
                    'order_id' => $order->id,
                    'stage' => "accepted",
                ]);
              
              $title = 'Order Accepted';
              $body = 'Your order has been accepted by the vendor' . $order->product->vendor->user->firstname . ' ' . $order->product->vendor->user->lastname;
              $data = [
                  'type' => 'single',
                  'user_id' => $order->agent->user_id,
                  'transaction_id' => $order->transaction_id
              ];

              $this->pushNotificationService->sendToUser($user, $title, $body, $data);

                return $this->success(['order' => $this->formatOrder($order)], 'Order accepted successfully');

            }

            return $this->error(null, 'Can only accept a pending order.', 404);

        }

        return $this->error(null, 'Order not  found.', 404);
    }

    public function acceptedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $orders = Order::where('status', 'accepted')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get()->map(fn($order) => $this->formatOrder($order));

        if ($orders->isEmpty()) {
            return $this->error(null, "No accepted orders found!", 404);
        }

        return $this->success(['orders' => $orders], 'All active accepted orders');
    }

    public function declineNew($order_id, EscrowService $escrowService)
    {
        $order = Order::with('product')->find($order_id);

        if($order){

            $user = User::where('id', $order->agent->user_id)->first();

            if($order->status != "pending"){
                return $this->error(null, "You can only decline a pending order.", 422);
            }

            if($order->payment_type == "wallet"){

                $defaultProvider = $this->GeneralWalletService->getDefaultWalletProviderForUser($user);

                $escrow = $escrowService->delineEscrow($order_id, $defaultProvider);

                return $escrow;

            }
            else{

                $order->status = "declined";
                $order->save();

                OrderProcessing::create([
                    'order_id' => $order->id,
                    'stage' => "declined",
                ]);
              
                $title = 'Order Declined';
                $body = 'Your order has been declined by the vendor' . $order->product->vendor->user->firstname . ' ' . $order->product->vendor->user->lastname;
                $data = [
                    'type' => 'single',
                    'user_id' => $order->agent->user_id,
                    'transaction_id' => $order->transaction_id
                ];

                $this->pushNotificationService->sendToUser($user, $title, $body, $data);

                return $this->success(['order' => $this->formatOrder($order)], 'Order declined successfully.');

            }

        }

        return response()->json([ 'status' => false, 'message' => "Order not found!"], 404);
    }

    public function declinedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $orders = Order::where('status', 'declined')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get()->map(fn($order) => $this->formatOrder($order));

        if ($orders->isEmpty()) {
            return $this->error(null, "No declined orders found!", 404);
        }

        return $this->success(['orders' => $orders], 'All declined orders');
    }

    public function confirmSupplied($order_id)
    {
        $order = Order::with('product')->find($order_id);

        if($order){

            if($order->status != "accepted"){
                return $this->error(null, "You can only confirm an accepted order.", 422);
            }

            $order->status = 'supplied';
            $order->save();
          
            OrderProcessing::create([
                'order_id' => $order->id,
                'stage' => "supplied",
            ]);

            $title = 'Order Supplied';
            $body = 'Your order has been supplied by the vendor' . $order->product->vendor->user->firstname . ' ' . $order->product->vendor->user->lastname;
            $data = [
                'type' => 'single',
                'user_id' => $order->agent->user_id,
                'transaction_id' => $order->transaction_id
            ];

            $this->pushNotificationService->sendToUser($user, $title, $body, $data);

            return $this->success(['order' => $this->formatOrder($order)], 'Order supplied.');

        }

        return $this->error(null, 'Order not  found.', 404);

    }

    public function suppliedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $orders = Order::where('status', 'supplied')->whereHas('product', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get()->map(fn($order) => $this->formatOrder($order));

        if($orders->isEmpty()) {
            return $this->error(null, "No active supplied orders found!", 404);
        }

        return $this->success(['orders' => $orders], 'List of active supplied orders');
    }

     public function completedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $orders = Order::where('status', 'completed')
            ->whereHas('product', function($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })->get()->map(fn($order) => $this->formatOrder($order));

        if ($orders->isEmpty()) {
            return $this->error(null, "No completed orders found!", 404);
        }

        return $this->success(['orders' => $orders], 'List of completed orders');
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

    private function formatOrder($order)
    {
        $total = number_format($order->quantity * $order->unit_price, 2, '.', ',');
        return [
            'id' => $order->id,
            'transaction_id' => $order->transaction_id,
            'product_name' => optional($order->product)->name,
            'product_image' => optional($order?->product?->product_images?->first())->image_path,
            'farmer' => optional($order->farmer)->fname . ' ' . optional($order->farmer)->lname,
            'quantity' => $order->quantity,
            'agent_price' => $order->unit_price,
            'total' => $total,
            'payment_type' => $order->payment_type,
            'delivery_type' => $order->delivery_type,
            'created_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
            'updated_date' => Carbon::parse($order->updated_at)->format('M j, Y, g:ia'),
            'status' => $order->status,
            'agent' => [
                'name' => optional($order->agent->user)->firstname . ' ' .
                        optional($order->agent->user)->lastname . ' ' .
                        optional($order->agent->user)->middlename,
                'phone_no' => optional($order->agent->user)->phone,
                'address' => optional($order->agent)->address,
            ]
        ];
    }

}
