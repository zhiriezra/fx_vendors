<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Order $order): bool
    {
        // user can see only orders for their vendor
        return $user->vendor && $user->vendor->id === $order->vendor_id;
    }

    public function update(User $user, Order $order): bool
    {
        // user can update only orders for their vendor
        return $user->vendor && $user->vendor->id === $order->vendor_id;
    }

    public function delete(User $user, Order $order): bool
    {
        // user can delete only orders for their vendor
        return $user->vendor && $user->vendor->id === $order->vendor_id;
    }
}