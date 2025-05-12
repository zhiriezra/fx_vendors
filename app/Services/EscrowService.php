<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Escrow;
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

    public function delineEscrow($order_id, $slug){

        $order = Order::find($order_id);

        if($order){

            $user_id = $order?->agent?->user_id;

            if($user_id != null){

                $user = User::where('id', $user_id)->first();

                return DB::transaction(function () use ($order, $user, $slug) {

                    $trans_id = 'EXA0CT' . now()->format('YmdHis') . rand(1000, 9999);

                    $amount = $order->unit_price * $order->quantity;

                    $amount = (float) $amount;

                    $orderParamTwo = [
                        'transaction_id'=>$trans_id,
                        'amount'=>$amount,
                        'user_id'=>$user->id, //this is an Agent not the auth user or Vendor
                    ];

                    $response = $this->GeneralWalletService->creditUserWallet($orderParamTwo);

                    $virtual_wallet = json_decode($response->getContent(), true);

                    if (
                        isset($virtual_wallet['data']) &&
                        isset($virtual_wallet['data']['data']['responseCode'])
                    ) {
                        if ($virtual_wallet['data']['data']['responseCode'] == "00") {

                        $meta = [
                                'type' => 'transaction',
                                'transaction_id' => $order->transaction_id,
                            ];

                            $user->walletDeposit($user->id, $slug, $amount, $meta);

                            $split_tran_id = explode('-', $order->transaction_id);

                            $order->update(['status' => 'declined']);

                            $check_not_completed_order = Order::where('transaction_id', 'LIKE', $split_tran_id[0] . '%')
                                            ->where('id', '!=', $order->id)
                                            ->whereIn('status', ['pending', 'accepted', 'supplied'])->first();

                                if($check_not_completed_order == null){

                                    $check_completed_order = Order::where('transaction_id', 'LIKE', $split_tran_id[0] . '%')
                                            ->where('id', '!=', $order->id)
                                                ->where('status', 'completed')->first();

                                    $escrow = Escrow::where('transaction_id', $split_tran_id[0] . '%')->first();

                                    if($check_completed_order != null){

                                        if($escrow  != null){
                                            $escrow->update(['status' => 'completed']);
                                        }
                                    }
                                    else{
                                        if($escrow  != null){
                                            $escrow->update(['status' => 'declined']);
                                        }
                                    }
                                }

                            $order = [
                                'id' => $order->id,
                                'agent' => $order->agent->user->firstname.' '.$order->agent->user->lastname,
                                'product_name' => $order->product->name,
                                'product_image' => optional($order->product->product_images->first())->image_path,
                                'farmer' => $order->farmer->fname.' '.$order->farmer->lname,
                                'quantity' => $order->quantity,
                                'agent_price' => $order->product->agent_price,
                                'created_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                                'updated_date' => Carbon::parse($order->created_at)->format('M j, Y, g:ia'),
                                'status' => $order->status
                            ];

                            return $this->success(['order' => $order], 'Order declined successfully');
                        }
                    }

                    $message = $virtual_wallet['message'] ?? 'An unexpected error occurred.';

                    return $this->error(null, $message, 401);

                });

            }

            return $this->error(null, 'Error processing refund.', 401);

        }

        return $this->error(null, 'Can not find this order', 401);
    }


}
