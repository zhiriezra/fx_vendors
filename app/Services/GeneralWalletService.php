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
    public function createUserWalletV1($user)
    {
        // Determine the default wallet provider for the user's country
        $defaultProvider = $this->getDefaultWalletProviderForUser($user);
        Log::info('Getting default wallet provider', [
            'user_id' => $user->id,
            'provider' => $defaultProvider
        ]);

        // Check if wallet already exists
        $existingWallet = Wallet::where('user_id', $user->id)
            ->where('slug', $defaultProvider)
            ->first();

        if ($existingWallet) {
            Log::info('Wallet already exists for user', [
                'user_id' => $user->id,
                'provider' => $defaultProvider
            ]);
            return $existingWallet;
        }

        // Resolve the wallet service for the default provider
        $walletService = $this->walletProviderFactory->make($defaultProvider);
        Log::info('Wallet service resolved', [
            'provider' => $defaultProvider
        ]);

        try {
            // Validate mandatory fields
            $missingFields = $walletService->validateMandatoryFields($user->id);
            Log::info('Validating mandatory fields', [
                'user_id' => $user->id,
                'missing_fields' => $missingFields
            ]);

            if (!empty($missingFields)) {
                Log::warning('Missing mandatory fields for wallet creation', [
                    'user_id' => $user->id,
                    'missing_fields' => $missingFields
                ]);
                return $this->error(400, 'Unable to create user wallet. Please update your profile. Missing fields: ' . implode(', ', $missingFields));
            }

            // Create the wallet through the provider's API
            $walletData = $walletService->createWallet($user->id);

            if (!isset($walletData['data'])) {
                Log::error('Invalid wallet data received from provider', [
                    'user_id' => $user->id,
                    'wallet_data' => $walletData
                ]);
                return $this->error(500, 'Invalid response from wallet provider');
            }

            Log::info('Wallet created through provider service', [
                'user_id' => $user->id,
                'wallet_data' => $walletData
            ]);

            // Create and save wallet in database
            $wallet = $this->createAndSaveWallet($user, $defaultProvider, $walletData);
            
            if (!$wallet) {
                Log::error('Failed to save wallet to database', [
                    'user_id' => $user->id,
                    'provider' => $defaultProvider
                ]);
                return $this->error(500, 'Failed to save wallet information');
            }

            Log::info('Wallet saved to database', [
                'user_id' => $user->id,
                'provider' => $defaultProvider,
                'wallet_id' => $wallet->id
            ]);

            return $wallet;

        } catch (\Exception $e) {
            Log::error('Exception during wallet creation', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Try to extract wallet data from exception message if available
            if (preg_match('/(\{.*\})/', $e->getMessage(), $matches)) {
                try {
                    $jsonPart = $matches[1];
                    $walletData = json_decode($jsonPart, true);

                    if (json_last_error() === JSON_ERROR_NONE && isset($walletData['data'])) {
                        Log::info('Extracted wallet data from exception', [
                            'user_id' => $user->id,
                            'wallet_data' => $walletData
                        ]);

                        $wallet = $this->createAndSaveWallet($user, $defaultProvider, $walletData);
                        
                        if ($wallet) {
                            Log::info('Wallet saved to database from exception data', [
                                'user_id' => $user->id,
                                'provider' => $defaultProvider,
                                'wallet_id' => $wallet->id
                            ]);
                            return $wallet;
                        }
                    }
                } catch (\Exception $jsonError) {
                    Log::error('Failed to process exception data', [
                        'user_id' => $user->id,
                        'error' => $jsonError->getMessage()
                    ]);
                }
            }

            return $this->error(null, 'Failed to create wallet: ' . $e->getMessage());
        }
    }

    public function createUserWalletOld($user)
    {
        // Determine the default wallet provider for the user's country
        $defaultProvider = $this->getDefaultWalletProviderForUser($user);

        // Resolve the wallet service for the default provider
        $walletService = $this->walletProviderFactory->make($defaultProvider);

        try {

            // Validate mandatory fields
            $missingFields = $walletService->validateMandatoryFields($user->id);

            if (!empty($missingFields)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unable to create user wallet. Please update your profile. Missing fields: ' . implode(', ', $missingFields),
                ], 400);
            }

            // Create the wallet
            $walletData = $walletService->createWallet($user->id);

            $this->createAndSaveWallet($user, $defaultProvider, $walletData);

            $wallet_balance = $user->walletBalance($user->id, $defaultProvider);

            return response()->json([
                'status'  => true,
                'message' => 'User wallet balance',
                'balance' => $wallet_balance,
            ], 201);

        } catch (\Exception $e) {

            $message = $e->getMessage();

            if (preg_match('/(\{.*\})/', $message, $matches)) {
                $jsonPart = $matches[1];

                $walletData = json_decode($jsonPart, true);
                return response()->json($walletData);
                if($walletData['data']['responseCode'] == "42"){

                    $this->createAndSaveWallet($user, $defaultProvider, $walletData);

                    $wallet_balance = $user->walletBalance($user->id, $defaultProvider);

                    return response()->json([
                        'status'  => true,
                        'message' => 'User wallet balance',
                        'balance' => $wallet_balance,
                    ], 201);
                }

            }

            return response()->json([
                'status'  => false,
                'message' => 'Error creating wallet: ' . $e->getMessage(),
            ], 422);

        }
    }

    /**
     * Create a new wallet for a user with improved error handling and logging
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUserWallet(User $user): \Illuminate\Http\JsonResponse
    {
        try {
            Log::info('Starting wallet creation process', ['user_id' => $user->id]);

            // Determine the default wallet provider for the user's country
            $defaultProvider = $this->getDefaultWalletProviderForUser($user);
            Log::info('Default provider determined', ['provider' => $defaultProvider, 'user_id' => $user->id]);

            // Resolve the wallet service for the default provider
            $walletService = $this->walletProviderFactory->make($defaultProvider);

            // Validate mandatory fields
            $missingFields = $walletService->validateMandatoryFields($user->id);
            if (!empty($missingFields)) {
                Log::warning('Missing mandatory fields for wallet creation', [
                    'user_id' => $user->id,
                    'missing_fields' => $missingFields
                ]);

                return $this->error(null,
                    'Unable to create user wallet. Please update your profile. Missing fields: ' . implode(', ', $missingFields),
                    'VALIDATION_ERROR',
                    400
                );
            }

            try {
                // Create the wallet
                $walletData = $walletService->createWallet($user->id);
                
            } catch (\Exception $e) {
                // Check if the error is about existing wallet (response code 42)
                if (strpos($e->getMessage(), '"responseCode":"42"') !== false) {
                    Log::info('Wallet already exists in provider system', [
                        'user_id' => $user->id,
                        'provider' => $defaultProvider
                    ]);

                    // Extract wallet data from error message
                    preg_match('/"data":({.*?})/', $e->getMessage(), $matches);
                    if (isset($matches[1])) {
                        $walletData = ['data' => json_decode($matches[1], true)];
                    } else {
                        throw $e; // Re-throw if we can't extract wallet data
                    }
                } else {
                    throw $e; // Re-throw if it's a different error
                }
            }

            // Check if wallet exists in database
            $existingWallet = Wallet::where('user_id', $user->id)
                                  ->where('slug', $defaultProvider)
                                  ->first();

            if (!$existingWallet) {
                // Save wallet to database if it doesn't exist
                $this->createAndSaveWallet($user, $defaultProvider, $walletData);
                Log::info('Wallet saved to database', [
                    'user_id' => $user->id,
                    'provider' => $defaultProvider
                ]);
            } else {
                Log::info('Wallet already exists in database', [
                    'user_id' => $user->id,
                    'provider' => $defaultProvider
                ]);
            }

            // Get wallet balance
            $walletBalance = $user->walletBalance($user->id, $defaultProvider);
            Log::info('Wallet balance retrieved', [
                'user_id' => $user->id,
                'balance' => $walletBalance
            ]);

            $responseData = [
                'balance' => $walletBalance,
                'provider' => $defaultProvider,
                'wallet_data' => $walletData,
                'is_new_wallet' => !$existingWallet
            ];

            return $this->success(
                $responseData,
                'Wallet created successfully',
                201
            );

        } catch (\InvalidArgumentException $e) {
            Log::error('Invalid argument exception in wallet creation', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return $this->error(
                $e->getMessage(),
                'INVALID_ARGUMENT',
                422
            );

        } catch (\Exception $e) {
            Log::error('Unexpected error in wallet creation', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Check if the error message contains a JSON response
            if (preg_match('/{"status":"(.*?)","message":"(.*?)"}/', $e->getMessage(), $matches)) {
                return $this->error(
                    $matches[2],
                    $matches[1],
                    422
                );
            }

            return $this->error($e->getMessage(), 'An unexpected error occurred while creating your wallet. Please try again later. INTERNAL_ERROR', 500);
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
            // if($result['responseCode'] == '00'){
            //     $walletTransaction->update([
            //         'status' => 'success',
            //     ]);

            //     return $data = [
            //         'status' => true,
            //         'message' => 'Withdrawal successful',
            //         'data' => $result,
            //     ];
            // }
            // else{
            //     $walletTransaction->update([
            //         'status' => 'failed',
            //     ]);

            //     return $data = [
            //         'status' => false,
            //         'message' => 'Withdrawal failed',
            //         'data' => $result,
            //     ];
            // }
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
