<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Escrow;
use App\Models\Vendor;
use App\Models\Wallet;
use App\Models\Product;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\DB;
use App\Services\GeneralWalletService;

class EscrowService{

    use ApiResponder;

    protected $GeneralWalletService;

    public function __construct(GeneralWalletService $GeneralWalletService)
    {
        $this->GeneralWalletService = $GeneralWalletService;
    }

    public function createEscrow(array $orderParam){

        $user = auth()->user();

        // Step 1: Check if the user already has a wallet
        $wallet = Wallet::where('holder_id', $user->id)->first();

        // Check user authentication and balance
        if ($wallet->balance < $orderParam['amount']) {
            return response()->json([
                'status' => false,
                'message' => 'Not enough funds in your wallet to perform this transaction.',
            ], 200);
        }

        DB::beginTransaction();

        try {

            // Create escrow record
            Escrow::create([
                'transaction_id' => $orderParam['transaction_id'],
                'agent_id' => $orderParam['agent_id'],
                'farmer_id' => $orderParam['farmer_id'],
            ]);

            DB::commit();

            return true;


        } catch (\Exception $e) {

            DB::rollBack();

            return $this->error($e->getMessage(), 'Transaction Error!!!', 500);

        }

    }

    public function releaseEscrow($order_id, $slug){

        $order = Order::find($order_id);

        if($order){

            $vendor = $order?->product?->vendor?->user_id;

            if($vendor != null){

                $user = User::where('id', $vendor)->first();

                DB::transaction(function () use ($order, $user, $slug) {

                    $product = Product::findOrFail($order->product_id);

                    if($product){

                        //$vendorUser = $product->vendor->user;
                        $trans_id = 'EXA0CT' . now()->format('YmdHis') . rand(1000, 9999);

                        $amount = $order->unit_price * $order->quantity;

                        $orderParamTwo = [
                            'transaction_id'=>$trans_id,
                            'amount'=>$amount,
                            'user_id'=>$user->id,
                            // This is not the auth user but the vendor, whose account is to be credited on completion of transaction
                        ];

                        $response = $this->GeneralWalletService->creditUserWallet($user, $orderParamTwo);

                        $virtual_wallet = json_decode($response->getContent(), true);

                        if (
                            isset($virtual_wallet['data']) &&
                            isset($virtual_wallet['data']['data']['responseCode'])
                        ) {
                                if ($virtual_wallet['data']['data']['responseCode'] == "00") {

                                    $user->walletDeposit($user->id, $slug, $amount);

                                    $order->update(['status' => 'completed']);

                                    $check_not_completed_order = Order::where('transaction_id', $order->transaction_id)
                                                ->whereIn('status', ['pending', 'accepted', 'supplied'])->first();

                                    if($check_not_completed_order == null){

                                        $escrow = Escrow::where('transaction_id', $order->transaction_id)->first();

                                        if($escrow  != null){
                                            $escrow->update(['status' => 'completed']);
                                        }
                                    }
                                }
                            } else {

                                $message = $virtual_wallet['message'] ?? 'An unexpected error occurred.';

                                return $this->error(null, $message, 401);

                            }

                    }else{
                        return $this->error(null, 'Can not find product', 401);
                    }

                });

                return $this->success(['order' => $order], 'Order successfully confirmed as received by agent');

            }
            else{
                return $this->error(null, 'Can not find vendor', 401);
            }

        }else{
                return $this->error(null, 'Can not find this order', 401);
            }

    }

    public function cancelEscrow($order_id, $slug)
    {
         $order = Order::where(['order_id' => $order_id, 'status' => 'pending'])->first();

         if($order){

            $user = User::where('id', auth()->id())->first();

            DB::transaction(function () use ($order, $user, $slug) {

                $product = Product::findOrFail($order->product_id);

                if($product){

                    //$vendorUser = $product->vendor->user;
                    $trans_id = 'EXA0CT' . now()->format('YmdHis') . rand(1000, 9999);

                    $amount = $order->unit_price * $order->quantity;

                    $orderParamTwo = [
                        'transaction_id'=>$trans_id,
                        'amount'=>$amount,
                        'user_id'=>$user->id,
                        // This is not the auth user but the vendor, whose account is to be credited on completion of transaction
                    ];

                    $response = $this->GeneralWalletService->creditUserWallet($user, $orderParamTwo);

                    $virtual_wallet = json_decode($response->getContent(), true);

                    if (
                        isset($virtual_wallet['data']) &&
                        isset($virtual_wallet['data']['data']['responseCode'])
                    ) {
                            if ($virtual_wallet['data']['data']['responseCode'] == "00") {

                                $user->walletDeposit($user->id, $slug, $amount);

                                $order->update(['status' => 'cancel']);

                                $check_not_completed_order = Order::where('transaction_id', $order->transaction_id)
                                            ->where('id', '!=', $order->id)
                                            ->whereIn('status', ['pending', 'accepted', 'supplied'])->first();

                                if($check_not_completed_order == null){

                                    $check_completed_order = Order::where('transaction_id', $order->transaction_id)
                                            ->where('id', '!=', $order->id)
                                                ->where('completed')->first();

                                    $escrow = Escrow::where('transaction_id', $order->transaction_id)->first();

                                    if($check_completed_order){

                                        if($escrow  != null){
                                            $escrow->update(['status' => 'completed']);
                                        }
                                    }
                                    else{
                                        if($escrow  != null){
                                            $escrow->update(['status' => 'cancelled']);
                                        }
                                    }

                                }

                            }
                        } else {

                            $message = $virtual_wallet['message'] ?? 'An unexpected error occurred.';

                            return $this->error(null, $message, 401);

                        }

                }else{
                    return $this->error(null, 'Can not find product', 401);
                }

            });

            return $this->success(['order' => $order], 'Order successfully cancelled.');


        }else{
                return $this->error(null, 'Can not find this order', 401);
            }
    }

    // public function removePercentage($order_id){

    //     $escrow = Escrow::where(['order_id' => $order_id, 'status' => 'pending'])->first();

    //     if ($escrow->status != 'pending') {
    //         return 'Escrow cannot be canceled';
    //     }

    //     DB::transaction(function () use ($escrow) {
    //         $user = User::findOrFail($escrow->user_id);
    //         $artisanAmount = $escrow->amount * 0.02;
    //         $userAmount = $escrow->amount - $artisanAmount;

    //         $user->deposit($userAmount);

    //         $artisan = User::findOrFail($escrow->artisan_id);
    //         $artisan->deposit($artisanAmount);

    //         $escrow->update(['status' => 'completed']);
    //     });

    // }


}
