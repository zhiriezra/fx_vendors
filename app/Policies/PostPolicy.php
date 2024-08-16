<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;


class PostPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Order $order)
    {
        // Check if the authenticated user is the owner of the vendor that received the order
        return $user->id === $order->vendor->user_id;
    }

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
}
