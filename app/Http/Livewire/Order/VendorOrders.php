<?php

namespace App\Http\Livewire\Order;

use Livewire\Component;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class VendorOrders extends Component
{

    public $orders;

    public function mount()
    {
        $user = User::where('id',Auth::id())->first();
        $this->orders = $user->vendor->orders()->where('status', 'pending')->get();
    }

    public function acceptOrder($orderId)
    {
        $order = Order::find($orderId);
        $product = $order->product;

        if ($product->quantity >= $order->quantity) {
            // Deduct the product quantity
            $product->quantity -= $order->quantity;
            $product->save();
    
            // Mark the order as accepted
            $order->status = 'accepted';
            $order->save();
            return redirect()->route('vendor.orders.accepted')->with('message', 'Order accepted successfully!');
        } else {
            session()->flash('message', 'Not enough product quantity');
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
