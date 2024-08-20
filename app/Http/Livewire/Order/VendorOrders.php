<?php

namespace App\Http\Livewire\Order;

use Livewire\Component;
use App\Models\Order;

class VendorOrders extends Component
{
    public $orders;

    public function mount()
    {
        $this->orders = Order::where('vendor_id', auth()->user()->vendor->id)
                        ->where('status', 'pending')
                        ->with('agent', 'product') 
                        ->get();
    }

    public function acceptOrder($orderId)
    {
        $order = Order::find($orderId);
        $product = $order->product;

        if ($product->quantity >= $order->requested_quantity) {
            // Deduct the product quantity
            $product->quantity -= $order->requested_quantity;
            $product->save();

            // Mark the order as accepted
            $order->status = 'accepted';
            $order->save();

            session()->flash('message', 'Order accepted successfully!');
        } else {
            session()->flash('error', 'Not enough quantity available!');
        }

        // Refresh orders
        $this->mount();
    }

    public function declineOrder($orderId)
    {
        $order = Order::find($orderId);
        $order->status = 'declined';
        $order->save();

        session()->flash('message', 'Order declined!');

        // Refresh orders
        $this->mount();
    }

    public function render()
    {
        return view('livewire.order.vendor-orders', [
            'orders' => $this->orders
        ]);
    }
}
