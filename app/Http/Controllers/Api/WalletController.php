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

        $wallet = Wallet::where('user_id', $this->user->id)
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
}
