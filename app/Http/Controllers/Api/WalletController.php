<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Models\PayoutRequest;
use App\Exports\TransactionsExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\GeneralWalletService;
use Illuminate\Support\Facades\Validator;
use App\Models\WalletTransaction;

class WalletController extends Controller
{
    use ApiResponder;

    protected GeneralWalletService $walletService;
    protected $user;
    protected $defaultProvider;

    public function __construct(GeneralWalletService $walletService)
    {
        $this->middleware(function ($request, $next) use ($walletService) {

            $this->user = User::where('id', auth()->user()->id)->first();

            $this->walletService = $walletService;

            $this->defaultProvider = $walletService->getDefaultWalletProviderForUser($this->user);

            return $next($request);
        });
    }

    /**
     * Retrieve the user's wallet balance or create a wallet if none exists.
     */
    public function getBalance(Request $request)
    {

        $wallet = Wallet::where('holder_id', $this->user->id)
            ->where('slug', $this->defaultProvider)
            ->first();

        if ($wallet) {

            $balance = $this->user->walletBalance($this->user->id, $this->defaultProvider);

            return $this->success(['balance' => $balance], 'User wallet balance.');
        }

        return $this->walletService->createUserWallet($this->user);
    }

    /**
     * Retrieve wallet information or create a new one if not found.
     */
    public function walletEnquiry(Request $request)
    {

        $wallet = Wallet::where('holder_id', $this->user->id)
            ->where('slug', $this->defaultProvider)
            ->first();

        if (!$wallet) {
            return $this->walletService->createUserWallet($this->user);
        }

        $formattedWallet = [
            'id'          => $wallet->id,
            'name'        => $wallet->name,
            'slug'        => $wallet->slug,
            'meta'        => $wallet->meta,
            'balance'     => $this->user->walletBalance($this->user->id, $this->defaultProvider),
            'created_at'  => Carbon::parse($wallet->created_at)->format('M j, Y, g:ia'),
            'updated_at'  => Carbon::parse($wallet->updated_at)->format('M j, Y, g:ia'),
        ];

        return $this->success(['wallet' => $formattedWallet], 'User default wallet.');

    }

    /**
     * Return the authenticated user's wallet transactions.
     */
    public function walletTransactions()
    {
        $wallet = Wallet::where('user_id', $this->user->id)->where('slug', $this->defaultProvider)->first();
        $transactions = WalletTransaction::where('wallet_id', $wallet->id)->get();
        
        return $this->success(['transactions' => $transactions], 'Wallet transactions.');
    }

    public function walletTransaction($transaction_id)
    {
        $transaction = WalletTransaction::where('transaction_id', $transaction_id)->first();

        return $this->success(['transaction' => $transaction], 'Wallet transaction.');
    }

/*     public function requestWithdrawal(Request $request){

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


    } */


    public function fundWithdraw(Request $request)
    {

        $rules = [
            'amount' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $trans_id = 'EXA0WD' . now()->format('YmdHis') . rand(1000, 9999);

        $param = [
                    'amount'=> $request->amount,
                    'transaction_id' => $trans_id
                ];

        $response = $this->walletService->walletFundWithdrawal($param);

        return $response;

        if (
                isset($response['data']) &&
                isset($response['data']['responseCode'])
            ) {
                if($response['data']['responseCode'] == "00"){
                    return $this->success(null, 'Fund Transfer Successful.');
                }
                else{

                        $message = $response['message'] ?? 'Transaction Failed.';

                        return $this->error(null, $message, 422);

                }
            }
            else{
                    $message = $response['message'] ?? 'Transaction Failed..';

                    return $this->error(null, $message, 422);
            }

    }

    public function exportTransactions()
    {
        $date = date('Y-m-d');
        $fileName = "transactions_{$date}.xlsx";

        return Excel::download(new TransactionsExport, $fileName);
    }
}
