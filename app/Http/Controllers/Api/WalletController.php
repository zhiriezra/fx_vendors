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
use App\Models\Bank;

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

    public function createWallet()
    {
        $requiredFields = ['dob', 'bvn', 'nin', 'permanent_address'];
        foreach ($requiredFields as $field) {
            if ($this->user->vendor->$field === NULL || $this->user->vendor->$field === '') {
                return $this->error(
                    [$field => ucfirst(str_replace('_', ' ', $field)) . ' is required'],
                    ucfirst(str_replace('_', ' ', $field)) . ' is required',
                    400
                );
            }
        }

        try {
            // Get user's country based on their type (vendor)
            $userCountry = $this->user->vendor->state->country->name;

            // For now, only allow wallet creation for Nigeria
            if ($userCountry !== 'Nigeria') {
                return $this->error('Wallet creation is currently only available for users in Nigeria.', 400);
            }

            // Check if user already has a wallet
            $existingWallet = Wallet::where('user_id', $this->user->id)
                ->where('slug', $this->defaultProvider)
                ->first();

            if ($existingWallet) {
                return $this->error(null, 'Wallet already exists for this user.', 400);
            }

            // Create wallet using the default provider for Nigeria (NPSB)
            $response = $this->walletService->createUserWallet($this->user);
            
            // If the response is already a JsonResponse, return it directly
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }

            // If we got a Wallet model instance, format and return it
            if ($response instanceof \App\Models\Wallet) {
                $formattedWallet = [
                    'id'          => $response->id,
                    'name'        => $response->name,
                    'slug'        => $response->slug,
                    'meta'        => $response->meta,
                    'balance'     => 0.00, // New wallet starts with zero balance
                    'account_name' => $response->account_name,
                    'account_number' => $response->account_number,
                    'reference' => $response->reference,
                    'customerId' => $response->customerId,
                    'response_code' => $response->response_code,
                    'created_at'  => Carbon::parse($response->created_at)->format('M j, Y, g:ia'),
                    'updated_at'  => Carbon::parse($response->updated_at)->format('M j, Y, g:ia'),
                ];

                return $this->success([
                    'wallet' => $formattedWallet
                ], 'Wallet created successfully.');
            }

            return $this->error(null,'Failed to create wallet. Please try again later.', 500);

        } catch (\Exception $e) {
            return $this->error(null, 'Failed to create wallet: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Retrieve the user's wallet balance or create a wallet if none exists.
     */
    public function getBalance(Request $request)
    {

        // get local wallet
        $wallet = Wallet::where('user_id', $this->user->id)
            ->where('slug', $this->defaultProvider)
            ->first();

        if (!$wallet) {
            return $this->error(null, 'Wallet not found for this user', 404);
        }

        try {
            // check 9PSB Wallet
            $response = $this->walletService->getActualBalance($this->user, $this->defaultProvider, $wallet->account_number);

            if($response['responseCode'] == '00'){
                // compare balance with local
                if($wallet->balance !== $response['data']['availableBalance']){
                    $wallet->balance = $response['data']['availableBalance'];
                    $wallet->save();
                }     
                
                $formattedWallet = [
                    'wallet' => $wallet->name,
                    'account_number' => $wallet->account_number,
                    'account_name' => $wallet->account_name,
                    'balance' => $wallet->balance,
                    'status' => $wallet->status,
                    'last_updated' => $wallet->updated_at
                ];

                return $this->success($formattedWallet, 'User wallet balance.', 200);
            }else{
                return $this->error($response, $response['message'], 400);
            }
        } catch (\Exception $e) {
    
            return $this->error($e, $e->getMessage(), 500);
        }      

        // return $this->walletService->createUserWallet($this->user);
    }

    /**
     * Retrieve wallet information or create a new one if not found.
     */
    public function walletEnquiry(Request $request)
    {

        $wallet = Wallet::where('user_id', $this->user->id)
            ->where('slug', $this->defaultProvider)
            ->first();

        if (!$wallet) {
            return $this->walletService->createUserWalletV1($this->user);
        }

        $formattedWallet = [
            'id'          => $wallet->id,
            'name'        => $wallet->name,
            'slug'        => $wallet->slug,
            'meta'        => $wallet->meta,
            'account_name' => $wallet->account_name,
            'account_number' => $wallet->account_number,
            'reference' => $wallet->reference,
            'customerId' => $wallet->customerId,
            'response_code' => $wallet->response_code,
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
    
    public function exportTransactions()
    {
        $date = date('Y-m-d');
        $fileName = "transactions_{$date}.xlsx";

        return Excel::download(new TransactionsExport, $fileName);
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        $vendor = auth()->user()->vendor;
        $bank = Bank::where('id', $vendor->bank)->first();
        if(empty($bank) || empty($vendor->account_name) || empty($vendor->account_no)){
            return $this->error(null, 'bank not found, please update your banking info', 404);
        }

        $wallet = Wallet::where('user_id', $this->user->id)->where('slug', $this->defaultProvider)->first();
        if (!$wallet || empty($wallet->account_number) || empty($wallet->account_name)) {
            return $this->error(null, 'Wallet not found or incomplete', 404);
        }

        $balance = $this->user->walletBalance($this->user->id, $this->defaultProvider);
        if ($request->amount > $balance) {
            return $this->error(null, 'Insufficient balance', 400);
        }
        
        $response = $this->walletService->walletFundWithdrawal($this->user, $request->amount, $wallet, $bank);

        if($response['status']){
            return $this->success($response['data'], $response['message'], 201);
        }
        else{
            return $this->error(null,'Withdrawal failed', 400);
        }
        
    }
}
