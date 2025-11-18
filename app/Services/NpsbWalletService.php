<?php

namespace App\Services;


use App\Models\Bank;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Wallet;
use App\Models\Country;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class NpsbWalletService
{
    // NPSB Response Codes
    const RESPONSE_SUCCESS = '00';
    const RESPONSE_WALLET_EXISTS = '42';
    const RESPONSE_INVALID_ACCOUNT = '01';
    const RESPONSE_INSUFFICIENT_FUNDS = '51';
    const RESPONSE_INVALID_AMOUNT = '13';
    const RESPONSE_SYSTEM_ERROR = '99';

    // NPSB Error Messages
    const ERROR_MESSAGES = [
        self::RESPONSE_SUCCESS => 'Transaction successful',
        self::RESPONSE_WALLET_EXISTS => 'A Wallet Already Exists For This User',
        self::RESPONSE_INVALID_ACCOUNT => 'Invalid account number',
        self::RESPONSE_INSUFFICIENT_FUNDS => 'Insufficient funds',
        self::RESPONSE_INVALID_AMOUNT => 'Invalid amount',
        self::RESPONSE_SYSTEM_ERROR => 'System error occurred'
    ];

    protected NpsbWalletApiClient $walletApiClient;

    public function __construct(NpsbWalletApiClient $walletApiClient)
    {
        $this->walletApiClient = $walletApiClient;
    }

    /**
     * Create a wallet for the user.
     *
     * @param int $userId
     * @return array
     * @throws \Exception
     */

    public function createWallet(int $userId)
    {
        // Generate the payload using a private method
        $payload = $this->generateWalletPayload($userId);
        // Call the /open_wallet API
        return $this->walletApiClient->post('/open_wallet', $payload);
    }

    /**
     * Debit a user's wallet.
     *
     * @param int $userId
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function debitWallet(int $userId, array $data): array
    {
        try {
            $accountNo = $this->getAccountNo($userId);
            if (!$accountNo) {
                throw new \Exception('Wallet account not found', self::RESPONSE_INVALID_ACCOUNT);
            }

            $narration = $this->getNarration($userId, 'debit');

            // Construct the payload specific to the 9PSB API
            $payload = [
                'accountNo' => $accountNo,
                'narration' => $narration,
                'totalAmount' => $data['amount'],
                'transactionId' => $data['transaction_id'],
                'merchant' => [
                    'isFee'              => false,
                    'merchantFeeAccount' => '',
                    'merchantFeeAmount'  => '',
                ],
            ];

            // Use the NpsbWalletApiClient to make the API call
            $response = $this->walletApiClient->post('/debit/transfer', $payload);

            // Check for specific NPSB response codes
            if (isset($response['data']['responseCode'])) {
                $responseCode = $response['data']['responseCode'];
                
                if ($responseCode !== self::RESPONSE_SUCCESS) {
                    throw new \Exception(
                        self::ERROR_MESSAGES[$responseCode] ?? 'Unknown error occurred',
                        $responseCode
                    );
                }
            }

            return $response;
        } catch (\Exception $e) {
            $this->logError('Failed to debit wallet', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }

    /**
     * Wallet Fund Transfet to Bank.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function walletWithdrawal(User $user, $amount, $reference, $wallet, $bank)
    {

            $narration = $this->getNarration($user->id, 'debit');
            // Construct the payload specific to the 9PSB API
            $payload = [
                'customer' => [
                    'account' =>[
                        'bank' => $bank->code,
                        'name' => auth()->user()->vendor->account_name,
                        'number' => auth()->user()->vendor->account_no,
                        'senderaccountnumber' => $wallet->account_number,
                        'sendername' => $wallet->account_name,
                    ]
                ],
                'narration' => $narration,
                'order' => [
                        'amount' => $amount,
                        'country' => $user->vendor->state->country->code,
                        'currency' => $user->vendor->state->country->currency,
                        'description' => 'Wallet Withdrawal',
                ],
                'transaction' => [
                    'reference' => $reference,
                ],
                'merchant' => [
                    'isFee'              => false,
                    'merchantFeeAccount' => '',
                    'merchantFeeAmount'  => '',
                ],
            ];

            // Use the NpsbWalletApiClient to make the API call
            $response = $this->walletApiClient->post('/wallet_other_banks', $payload);

            if (!isset($response['responseCode'])) {
                // Transport fine, but payload malformed
                throw new Exception('Malformed response from NPSB.', 502);
            }
    
            return $response;
        
    }

     /** Debit a user's wallet.
     *
     * @param int $userId
     * @param array $data
     * @return array
     * @throws \Exception
     */
    
     public function creditWallet(int $userId, array $data): array
    {
        try {
            $accountNo = $this->getAccountNo($userId);
            if (!$accountNo) {
                throw new \Exception('Wallet account not found', self::RESPONSE_INVALID_ACCOUNT);
            }

            $narration = $this->getNarration($userId, 'credit');

            // Construct the payload specific to the 9PSB API
            $payload = [
                'accountNo' => $accountNo,
                'narration' => $narration,
                'totalAmount' => $data['amount'],
                'transactionId' => $data['transaction_id'],
                'merchant' => [
                    'isFee'              => false,
                    'merchantFeeAccount' => '',
                    'merchantFeeAmount'  => '',
                ],
            ];

            // Use the NpsbWalletApiClient to make the API call
            $response = $this->walletApiClient->post('/credit/transfer', $payload);

            // Check for specific NPSB response codes
            if (isset($response['data']['responseCode'])) {
                $responseCode = $response['data']['responseCode'];
                
                if ($responseCode !== self::RESPONSE_SUCCESS) {
                    throw new \Exception(
                        self::ERROR_MESSAGES[$responseCode] ?? 'Unknown error occurred',
                        $responseCode
                    );
                }
            }

            return $response;
        } catch (\Exception $e) {
            $this->logError('Failed to credit wallet', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }

    /**
     * Log wallet-related errors.
     *
     * @param string $message
     * @param array $context
     */
    public function logError(string $message, array $context = []): void
    {
        Log::error("[WalletService] $message", $context);
    }

    public function getAccountNo($user_id){

        $wallet = Wallet::where('user_id', $user_id)->where('slug', 'npsb')->first();

        $accountNumber = $wallet['meta']['accountNumber'] ?? null;

        return $accountNumber;
    }

    public function getFullName($user_id){

        $wallet = Wallet::where('user_id', $user_id)->where('slug', 'npsb')->first();

        $fullName = $wallet['meta']['fullName'] ?? null;

        return $fullName;
    }

    public function getNarration($user_id, $for)
    {
        $walletType = $for == "credit" ? "CREDIT_WALLET" : "DEBIT_WALLET";

        $acc_no = $this->getAccountNo($user_id);

        $fullname = $this->getFullName($user_id);

        $datetime = date('YmdHi');

        $randomDigits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $result = $datetime . $randomDigits;

        $narration = $fullname . "/" . $walletType . "/" . $acc_no . "/WAAS" . $result;

        return $narration;
    }


    /**
     * Validate mandatory fields for wallet creation.
     *
     * @param int $userId
     * @return array
     */
    public function validateMandatoryFields(int $userId): array
    {

        $user = User::find($userId);
        $agent = Vendor::where('user_id', $userId)->first();

        $userMandatoryFields = ['firstname', 'lastname', 'phone', 'email'];
        $missingUserFields = [];

        foreach ($userMandatoryFields as $field) {
            if (empty($user->$field)) {
                $missingUserFields[] = $field;
            }
        }

        $agentMandatoryFields = ['gender', 'bvn', 'dob', 'permanent_address'];
        $missingAgentFields = [];

        foreach ($agentMandatoryFields as $field) {
            if (empty($agent->$field)) {
                $missingAgentFields[] = $field;
            }
        }

        // Combine missing fields from both tables
        return array_merge($missingUserFields, $missingAgentFields);
    }

        /**
     * Generate the payload for creating a wallet.
     *
     * @param int $userId
     * @return array
     */
    private function generateWalletPayload(int $userId): array
    {
        // Retrieve user and agent records
        $user = User::find($userId);
        $vendor = Vendor::where('user_id', $userId)->first();

        // Validate that user and agent exist
        if (!$user || !$vendor) {
            throw new \Exception("User or agent record not found for user ID: $userId");
        }

        $genderBool = strtolower($vendor->gender) === 'male' ? 0 : 1;

        $dobFormatted = \Carbon\Carbon::parse($vendor->dob)->format('d/m/Y');

        return [
            'transactionTrackingRef' => Str::uuid(), // Unique transaction reference
            'otherNames'             => trim($user->firstname . ' ' . ($user->middlename ?? '')),
            'lastName'               => $user->lastname,
            'phoneNo'                => $user->phone,
            'email'                  => $user->email,
            'gender'                 => $genderBool,
            'bvn'                    => $vendor->bvn,
            'dateOfBirth'            => $dobFormatted,
            'address'                => $vendor->permanent_address,
        ];
    }

    public function getActualBalance($user, $account_number)
    {
        $payload = [
            'accountNo' => $account_number,
        ];
        // Fetch the actual balance from the NPSB API
        $response = $this->walletApiClient->post('/wallet_enquiry', $payload);
        return $response;
    }
}
