<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Escrow;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;


class OrderController extends Controller
{
    use ApiResponder;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         $vendorId = auth()->user()->vendor->id;

        $escrows = Escrow::with(['orders.product.vendor.user', 'orders.agent.user', 'vendor.user'])->get();

        $filteredEscrows = $escrows->filter(fn(Escrow $e) => $this->hasOrderForVendor($e, $vendorId));

        if ($filteredEscrows->isEmpty()) {
            return $this->error(null, "No orders found!", 404);
        }

        return $this->success(['orders' => $filteredEscrows->map(fn($e) => $this->formatEscrow($e))], 'All orders');

    }

    public function singleOrder($escrow_id)
    {
        $vendorId = auth()->user()->vendor->id;

        $escrow = Escrow::where('id', $escrow_id)
            ->with(['orders.product.vendor.user', 'orders.agent.user', 'vendor.user'])
            ->first();

        if (!$escrow || !$this->hasOrderForVendor($escrow, $vendorId)) {
            return $this->error(null, "No order found.", 404);
        }

        return $this->success(['order' => $this->formatEscrow($escrow)], 'Single Order');
    }


    public function pendingOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $escrows = Escrow::where('status', 'pending')
            ->with(['orders.product.vendor.user', 'orders.agent.user', 'vendor.user'])->get();

        $filteredEscrows = $escrows->filter(fn(Escrow $e) => $this->hasOrderForVendor($e, $vendorId));

        if ($filteredEscrows->isEmpty()) {
            return $this->error(null, "No pending orders found!", 404);
        }

        return $this->success(['orders' => $filteredEscrows->map(fn($e) => $this->formatEscrow($e))], 'Pending orders');
    }

    public function acceptedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $escrows = Escrow::where('status', 'accepted')
            ->with(['orders.product.vendor.user', 'orders.agent.user', 'vendor.user'])->get();

        $filteredEscrows = $escrows->filter(fn(Escrow $e) => $this->hasOrderForVendor($e, $vendorId));

        if ($filteredEscrows->isEmpty()) {
            return $this->error(null, "No accepted orders found!", 404);
        }

        return $this->success(['orders' => $filteredEscrows->map(fn($e) => $this->formatEscrow($e))], 'Accepted orders');
    }

    public function declinedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $escrows = Escrow::where('status', 'declined')
            ->with(['orders.product.vendor.user', 'orders.agent.user', 'vendor.user'])->get();

        $filteredEscrows = $escrows->filter(fn(Escrow $e) => $this->hasOrderForVendor($e, $vendorId));

        if ($filteredEscrows->isEmpty()) {
            return $this->error(null, "No declined orders found!", 404);
        }

        return $this->success(['orders' => $filteredEscrows->map(fn($e) => $this->formatEscrow($e))], 'Declined orders');
    }

    public function suppliedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $escrows = Escrow::where('status', 'supplied')
            ->with(['orders.product.vendor.user', 'orders.agent.user', 'vendor.user'])->get();

        $filteredEscrows = $escrows->filter(fn(Escrow $e) => $this->hasOrderForVendor($e, $vendorId));

        if ($filteredEscrows->isEmpty()) {
            return $this->error(null, "No supplied orders found!", 404);
        }

        return $this->success(['orders' => $filteredEscrows->map(fn($e) => $this->formatEscrow($e))], 'Supplied orders');
    }

     public function completedOrders()
    {
        $vendorId = auth()->user()->vendor->id;

        $escrows = Escrow::where('status', 'completed')
            ->with(['orders.product.vendor.user', 'orders.agent.user', 'vendor.user'])->get();

        $filteredEscrows = $escrows->filter(fn(Escrow $e) => $this->hasOrderForVendor($e, $vendorId));

        if ($filteredEscrows->isEmpty()) {
            return $this->error(null, "No completed orders found!", 404);
        }

        return $this->success(['orders' => $filteredEscrows->map(fn($e) => $this->formatEscrow($e))], 'Completed orders');
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

    /**
     * Formats a single escrow for API response.
     */
    private function formatEscrow($escrow)
    {
        return [
            'id' => $escrow->id,
            'transaction_id' => $escrow->transaction_id,
            'total_amount' => (float) $escrow->total,
            'payment_type' => $escrow->payment_type,
            'delivery_type' => $escrow->delivery_type,
            'vendor' => optional($escrow->vendor?->user)->firstname . ' ' . optional($escrow->vendor?->user)->lastname,
            'agent' => [
                'name' => optional(optional($escrow->agent?->user))->firstname . ' ' .
                            optional(optional($escrow->agent?->user))->lastname . ' ' .
                            optional(optional($escrow->agent?->user))->middlename,
                'phone_no' => optional(optional($escrow->agent?->user))->phone,
                'address' => optional($escrow->agent)->permanent_address,
            ],
            'created_date' => Carbon::parse($escrow->created_at)->format('M j, Y, g:ia'),
            'updated_date' => Carbon::parse($escrow->updated_at)->format('M j, Y, g:ia'),
            'status' => $escrow->status,
            'products' => $escrow->orders->map(function ($order) {
                $total = (float) $order->quantity * $order->unit_price;
                return [
                    'id' => $order->id,
                    'product_name' => optional($order->product)->name,
                    'product_image' => optional(optional($order->product)->product_images->first())->image_path,
                    'farmer' => optional($order->farmer)->fname . ' ' . optional($order->farmer)->lname,
                    'quantity' => $order->quantity,
                    'unit_price' => (float) $order->unit_price,
                    'commission' => (float) $order->commission,
                    'total' => $total,
                ];
            }),
        ];
    }

}
