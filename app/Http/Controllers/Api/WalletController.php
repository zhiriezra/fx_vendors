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

            if($payoutRequest){
                return response()->json(['status' => true, 'message' => 'Withdrawal request successful, funds will be disbursed within 24 hours', 'data' => ['withdrawal_request' => $payoutRequest]], 200);
            }

            return response()->json(['status' => false, 'message' => 'There was an error processing your withdrawal, please try again'], 400);

        }

        return response()->json(['status' => false, 'message' => 'You do not have enough funds in your wallet'], 400);

    }

    public function withdrawalRequests(){
        $payoutRequests = PayoutRequest::where('vendor_id', auth()->user()->vendor->id)->get();

        return response()->json(['status' => true, 'message' => 'Withdrawal requests', 'data' => ['requests' => $payoutRequests]], 200);

    }

}
