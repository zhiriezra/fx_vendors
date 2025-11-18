<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletType;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Factories\WalletProviderFactory;
use App\Models\User;
use App\Traits\ApiResponder;
use App\Models\WalletTransaction;
use App\Models\Bank;

class GeneralWalletService
{
    use ApiResponder;

    protected $walletProviderFactory;

    public $userCountry = null;

    public function __construct(WalletProviderFactory $walletProviderFactory)
    {
        $this->walletProviderFactory = $walletProviderFactory;
    }

    /**
     * Check if the agent already has a wallet.
     *
     * @param int $userId
     * @return bool
     */
    public function vendorHasWallet(int $userId): bool
    {
        $user = User::where('id', $userId)->first();

        $defaultProvider = $this->getDefaultWalletProviderForUser($user);

        return Wallet::where('user_id', $userId)->where('slug', $defaultProvider)->exists();
    }

    /**
     * Get the default wallet provider for the user's country.
     *
     * @param \App\Models\User $user
     * @return string
     */
    public function getDefaultWalletProviderForUser($user): string
    {
        $countryToProviderMap = config('wallet_providers.country_to_provider_map');

        if($user->user_type_id == 1){
            $userCountry = $user?->agent?->state?->country?->name;
        }
        else{
            $userCountry = $user?->vendor?->state?->country?->name;
        }

        if ($userCountry == null) {
            throw new \Exception("User country information is missing or incomplete.");
        }

        if (!isset($countryToProviderMap[$userCountry])) {
            throw new \InvalidArgumentException("No default wallet provider found for country: $userCountry");
        }

        return $countryToProviderMap[$userCountry];
    }

    public function debitUserWallet(User $user, array $data)
    {
        try {
            // Determine the default wallet provider for the user's country
            $defaultProvider = $this->getDefaultWalletProviderForUser($user);

            // Resolve the wallet service for the default provider
            $walletService = $this->walletProviderFactory->make($defaultProvider);

            // Call the wallet service to debit the wallet from the API call
            $result = $walletService->debitWallet($user->id, $data);

            if($result['data']['responseCode'] == "00"){

                return response()->json([
                    'status'  => true,
                    'message' => 'Wallet debited successfully',
                    'data'    => $result,
                ], 200);

            }
            else{

                return response()->json([
                    'status'  => false,
                    'message' => 'Transaction Failed',
                    'data'    => $result,
                ], 422);

            }

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to debit wallet', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to debit wallet: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function creditUserWallet(array $data){
        try {

            $user = User::where('id', $data['user_id'])->first();

            // Determine the default wallet provider for the user's country
            $defaultProvider = $this->getDefaultWalletProviderForUser($user);

            // Resolve the wallet service for the default provider
            $walletService = $this->walletProviderFactory->make($defaultProvider);

            // Call the wallet service to debit the wallet from the API call
            $result = $walletService->creditWallet($user->id, $data);

            return $result;

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to process payment', [
                'user_id' => $data['user_id'], //$user->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to process payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new wallet for a user with improved error handling and logging
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Create a new wallet for a user with improved error handling and logging
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUserWallet(User $user): \Illuminate\Http\JsonResponse
    {
            Log::info('Starting wallet creation process', ['user_id' => $user->id]);

            // Determine the default wallet provider for the user's country
            $defaultProvider = $this->getDefaultWalletProviderForUser($user);
            Log::info('Default provider determined', ['provider' => $defaultProvider, 'user_id' => $user->id]);

            // Check if wallet exists in database
            $existingWallet = Wallet::where('user_id', $user->id)
                                  ->where('slug', $defaultProvider)
                                  ->first();

            if($existingWallet){
                return $this->error(null, 'Wallet already exists for this user.', 400);
            }

            // Resolve the wallet service for the default provider
            $walletService = $this->walletProviderFactory->make($defaultProvider);

            // Validate mandatory fields
            $missingFields = $walletService->validateMandatoryFields($user->id);
            if (!empty($missingFields)) {
                Log::warning('Missing mandatory fields for wallet creation', [
                    'user_id' => $user->id,
                    'missing_fields' => $missingFields
                ]);

                return $this->error(null, 'Unable to create user wallet. Please update your profile. Missing fields: ' . implode(', ', $missingFields), 400);
            }

            // Create the wallet
            $walletData = $walletService->createWallet($user->id);
            $responseCode = $walletData['data']['responseCode'] ?? null;

            if($responseCode == "00"){
                
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'name' => $defaultProvider,
                    'slug' => $defaultProvider,
                    'balance' => 0,
                    'meta' => json_encode($walletData['data'] ?? []),   
                    'account_name' => $walletData['data']['fullName'],
                    'account_number' => $walletData['data']['accountNumber'],
                    'reference' => $walletData['data']['orderRef'] ?? null,
                    'customerId' => $walletData['data']['customerID'] ?? null,
                    'response_code' => $walletData['data']['responseCode'] ?? null,
                    'status' => true
                ]);

                Log::info('Wallet created successfully', [
                    'user_id' => $user->id,
                    'default_provider' => $defaultProvider,
                    'wallet_data' => $walletData,
                    'wallet' => $wallet,
                ]);

                return $this->success($wallet, 'Wallet created successfully', 201);

            }elseif($responseCode == "42"){
               
                $wallet = Wallet::where('user_id', $user->id)->where('slug', $defaultProvider)->first();

                Log::info('Wallet found', [
                    'user_id' => $user->id,
                    'default_provider' => $defaultProvider,
                    'wallet' => $wallet,
                ]);

                if(!$wallet){

                    Log::info('Wallet not found', [
                        'user_id' => $user->id,
                        'default_provider' => $defaultProvider,
                        'wallet_data' => $walletData,
                    ]);

                    return Wallet::create([
                        'user_id' => $user->id,
                        'name' => $defaultProvider,
                        'slug' => $defaultProvider,
                        'balance' => 0,
                        'meta' => json_encode($walletData['data'] ?? []),   
                        'account_name' => $walletData['data']['fullName'] ?? null,
                        'account_number' => $walletData['data']['accountNumber'] ?? null,
                        'reference' => $walletData['data']['orderRef'] ?? null,
                        'customerId' => $walletData['data']['customerID'] ?? null,
                        'response_code' => $walletData['data']['responseCode'] ?? null,
                        'status' => true
                    ]);

                    Log::info('Wallet created', [
                        'user_id' => $user->id,
                        'default_provider' => $defaultProvider,
                        'wallet' => $wallet,
                    ]);

                    return $this->success($wallet, 'Wallet created successfully', 201);
                }
        
                $wallet->meta = json_encode($walletData['data'] ?? []);
                $wallet->account_name = $walletData['data']['fullName'] ?? null;
                $wallet->account_number = $walletData['data']['accountNumber'] ?? null;
                $wallet->reference = $walletData['data']['orderRef'] ?? null;
                $wallet->customerId = $walletData['data']['customerID'] ?? null;
                $wallet->response_code = $walletData['data']['responseCode'] ?? null;
                $wallet->save();

                Log::info('Wallet updated', [
                    'user_id' => $user->id,
                    'default_provider' => $defaultProvider,
                    'wallet' => $wallet,
                ]);
                        
                return $this->success($wallet, 'Wallet updated successfully', 200);

            }else{
                Log::error('Failed to create wallet', [
                    'user_id' => $user->id,
                    'error'   => $walletData['message'] ?? 'Failed to create wallet',
                ]);
                return $this->error(null, $walletData['message'] ?? 'Failed to create wallet', 400);
            }

    }

    private function createAndSaveWallet($user, $defaultProvider, $walletData)
    {

        $wallet_type = WalletType::where('slug', $defaultProvider)->first();

        if (!$wallet_type) {
            Log::error("Wallet type not found for provider: $defaultProvider", ['user_id' => $user->id]);
            throw new \Exception("Wallet type not found for provider: $defaultProvider");
        }

        try {
            return Wallet::create([
                'user_id' => $user->id,
                'name' => $defaultProvider,
                'slug' => $defaultProvider,
                'balance' => 0,
                'meta' => json_encode($walletData['data'] ?? []),   
                'account_name' => $walletData['data']['fullName'] ?? null,
                'account_number' => $walletData['data']['accountNumber'] ?? null,
                'reference' => $walletData['data']['orderRef'] ?? null,
                'customerId' => $walletData['data']['customerID'] ?? null,
                'response_code' => $walletData['data']['responseCode'] ?? null,
                'status' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create wallet record', [
                'user_id' => $user->id,
                'provider' => $defaultProvider,
                'error' => $e->getMessage()
            ]);
            return null;
        }

    }

    public function walletFundWithdrawal(User $user, $amount, $wallet, $bank){
        try{
            // Determine the default wallet provider for the user's country
            $defaultProvider = $this->getDefaultWalletProviderForUser($user);
            
            $reference = 'WDL-'.uniqid();
            $walletTransaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'withdraw',
                'status' => 'pending',
                'description' => 'Wallet Withdrawal',
                'payment_reference' => $reference,
            ]);

            // Resolve the wallet service for the default provider
            $walletService = $this->walletProviderFactory->make($defaultProvider);

            // Call the wallet service to debit the wallet from the API call
            $result = $walletService->walletWithdrawal($user, $amount, $reference, $wallet, $bank);
            return $result;
            
        }catch(Exception $e){
            $this->error(null, $e->getMessage(), 503);
        }
    }

    public function getActualBalance($user, $defaultProvider, $account_number)
    {
       

            // Determine the default wallet provider for the user's country
            $defaultProvider = $this->getDefaultWalletProviderForUser($user);

            // Resolve the wallet service for the default provider
            $walletService = $this->walletProviderFactory->make($defaultProvider);

            // Call the wallet service to get the actual balance
            return $walletService->getActualBalance($user, $account_number);

        
    }

}
