<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Wallet;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Models\PayoutRequest;
use App\Exports\TransactionsExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\GeneralWalletService;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    use ApiResponder;

    protected $GeneralWalletService;

    public function __construct(GeneralWalletService $GeneralWalletService)
    {
        $this->GeneralWalletService = $GeneralWalletService;
    }

    /**
     * Get the user's wallet balance or create a wallet if it doesn't exist.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalance(Request $request)
    {
        $user = $request->user();

        $defaultProvider = $this->GeneralWalletService->getDefaultWalletProviderForUser($user);

        // Check if the user already has a wallet
        $wallet = Wallet::where('holder_id', $user->id)->where('slug', $defaultProvider)->first();

        if ($wallet) {
            // Return the wallet information
            return $this->success(['balance' => $wallet->balance], 'User wallet balance.');
        }

        //Create a wallet for the user if it doesn't exist
        return $this->GeneralWalletService->createUserWallet($user);
    }


    public function walletEnquiry(Request $request)
    {
        $user = $request->user();

        $defaultProvider = $this->GeneralWalletService->getDefaultWalletProviderForUser($user);

        // Check if the user already has a wallet
        $wallet = Wallet::where('holder_id', $user->id)->where('slug', $defaultProvider)->first();

        if ($wallet) {
            // Return the wallet information
            return $this->success(['wallet' => $wallet], 'User default wallet.');
        }

        // Create a wallet for the user if it doesn't exist
        return $this->GeneralWalletService->createUserWallet($user);
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

    public function transactions(){

        $transactions = auth()->user()->transactions->map(function($transaction){
            return [
                'wallet_id' => $transaction->wallet_id,
                'user_id' => $transaction->payable_id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'meta' => $transaction->meta,
                'created_at' => Carbon::parse($transaction->created_at)->format('M j, Y, g:ia'),
                'updated_at' => Carbon::parse($transaction->updated_at)->format('M j, Y, g:ia')

            ];
        });

        return $this->success(['transactions' => $transactions], 'My recent transactions.');

    }

    public function exportTransactions()
    {
        $date = date('Y-m-d');
        $fileName = "transactions_{$date}.xlsx";

        return Excel::download(new TransactionsExport, $fileName);
    }
}
