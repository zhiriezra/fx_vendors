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
        // user can see only their own orders
        return $user->id === $order->user_id;
    }

    public function update(User $user, Order $order): bool
    {
        // user can update only their own orders (if you permit updates at all)
        return $user->id === $order->user_id;
    }

    public function delete(User $user, Order $order): bool
    {
        // same logic – delete only own orders
        return $user->id === $order->user_id;
    }
}