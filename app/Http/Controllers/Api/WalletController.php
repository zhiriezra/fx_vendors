<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function getBalance(Request $request){
        $balance = $request->user()->balance;

        return response()->json(['status' => true, 'message' => 'User wallet balance', 'data' =>['balance' => $balance]], 200);
    }

    public function requestWithdrawal(Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => 'required'
        ]);

        $walletBalance = $request->user()->balance;
        $amount = floatval($request->amount);

        if($validator->fails()){
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $todayWithdrawal = PayoutRequest::where(['vendor_id' => auth()->user()->vendor->id, 'status' => 'pending'])->whereDate('created_at', Carbon::today())->exists();

        if($todayWithdrawal) {
            return response()->json(['status' => false, 'message' => 'You have a pending withdrawal request today.'], 400);
        }

        if($amount <= $walletBalance ){
            $payoutRequest = PayoutRequest::create([
                'vendor_id' => auth()->user()->vendor->id,
                'amount' => $request->amount,
            ]);
            $data = [
                'id' => $payoutRequest->id,
                'vendor_id' => $payoutRequest->vendor_id,
                'amount' => $payoutRequest->amount,
                'created_at' => Carbon::parse($payoutRequest->created_at)->format('M j, Y, g:ia'),
                'updated_at' => Carbon::parse($payoutRequest->updated_at)->format('M j, Y, g:ia')
            ];

            if($payoutRequest){
                return response()->json(['status' => true, 'message' => 'Withdrawal request successful, funds will be disbursed within 24 hours', 'data' => ['withdrawal_request' => $data]], 200);
            }

            return response()->json(['status' => false, 'message' => 'There was an error processing your withdrawal, please try again'], 400);

        }

        return response()->json(['status' => false, 'message' => 'You do not have enough funds in your wallet'], 400);

    }

    public function withdrawalRequests(){
        $payoutRequests = PayoutRequest::where('vendor_id', auth()->user()->vendor->id)->get()->map(function($payout){
            return [
                'id' => $payout->id,
                'vendor_id' => $payout->vendor_id,
                'amount' => $payout->amount,
                'transaction_reference' => null,
                'status' => $payout->status,
                'created_at' => Carbon::parse($payout->created_at)->format('M j, Y, g:ia'),
                'updated_at' => Carbon::parse($payout->updated_at)->format('M j, Y, g:ia')

            ];
        });

        return response()->json(['status' => true, 'message' => 'Withdrawal requests', 'data' => ['requests' => $payoutRequests]], 200);

    }

    public function transactions(){

        $transactions = auth()->user()->transactions->map(function($transaction){
            return [
                'wallet_id' => $transaction->wallet_id,
                'user_id' => $transaction->payable_id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'created_at' => Carbon::parse($transaction->created_at)->format('M j, Y, g:ia'),
                'updated_at' => Carbon::parse($transaction->updated_at)->format('M j, Y, g:ia')

            ];
        });
        return response()->json(['status' => true, 'message' => 'My recent transactions', 'data' => ['transactions' => $transactions]], 200);

    }
}
