<?php

namespace App\Http\Livewire\Order;

use Livewire\Component;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class VendorAccepted extends Component
{
    public $orders;

    public function mount()
    {
        $user = User::where('id',Auth::id())->first();
        $this->orders = $user->vendor->orders()->where('status', 'accepted')->get();
    }

    public function supplyOrder($orderId)
    {
        $order = Order::find($orderId);
        $product = $order->product;

        $order->status = 'supplied';
        $order->save();
        return redirect()->route('vendor.orders.supplied')->with('message', 'Order Supplied successfully, awaiting confirmation');

        
    }

    public function render()
    {
        return view('livewire.order.vendor-accepted', [
            'orders' => $this->orders
        ]);
    }
}
