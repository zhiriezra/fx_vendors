<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\WalletAuth\NpsbAuthenticator;
use Illuminate\Support\Facades\Log;

class NpsbWalletApiClient
{
    protected string $baseUrl;
    protected $authenticator;

    public function __construct(string $baseUrl, NpsbAuthenticator $authenticator)
    {
        $this->baseUrl = $baseUrl;
        $this->authenticator = $authenticator;
    }

    /**
     * Make a generic POST request to the NPSB API.
     *
     * @param string $endpoint
     * @param array $payload
     * @return array
     * @throws \Exception
     */
    public function post(string $endpoint, array $payload): array
    {
        $headers = $this->getAuthorizationHeaders();

        $response = Http::withHeaders($headers)->post($this->baseUrl . $endpoint, $payload);

        if (!$response->successful()) {
            throw new \Exception("API request to '$endpoint' failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Make a generic GET request to the NPSB API.
     *
     * @param string $endpoint
     * @param array $queryParams
     * @return array
     * @throws \Exception
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        $headers = $this->getAuthorizationHeaders();

        $response = Http::withHeaders($headers)->get($this->baseUrl . $endpoint, $queryParams);

        $responseData = $response->json();

        if (!$response->successful()) {
            throw new \Exception("API GET request to '$endpoint' failed: " . $response->body());
        }

        if (!isset($responseData['data'])) {
            throw new \Exception('Invalid API response: Missing "data" key.');
        }

        return $responseData;
    }


    /**
     * Call an API to fetch wallet balance.
     *
     * @param string $walletNumber
     * @return float
     * @throws \Exception
     */
    public function getWalletBalance(string $walletNumber)
    {
        $response = $this->post('/wallet_enquiry', ['accountNo' => $walletNumber]);

        $responseCode = $this->getFromResponse($response, 'responseCode');

        if ($responseCode === "00") {
            $balance = $this->getFromResponse($response, 'data.availableBalance', 0);
            return (float) $balance;
        }

        Log::error('Failed to fetch wallet balance', [
            'wallet_number' => $walletNumber,
            'response' => $response
        ]);

        return null;
    }



    /**
     * Call an API to fetch wallet balance.
     *
     * @param string $walletNumber
     * @return float
     * @throws \Exception
     */
    public function getWalletStatus(string $walletNumber): string
    {
        // Use the generic get method to fetch wallet balance
        $response = $this->get('/wallet_status', ['accountNo' => $walletNumber]);

        // Return the balance from the response
        return $response['data']['walletStatus'];
    }

    /**
     * Call an API to fetch the list of banks.
     *
     * @return array
     * @throws \Exception
     */
    public function getBanks(): array
    {
        // Use the generic get method to fetch the list of banks
        $response = $this->get('/get_banks');

        // Ensure the 'data' key exists and contains the 'bankList'
        if (!isset($response['data']['bankList'])) {
            throw new \Exception('Invalid API response: Missing "bankList" key.');
        }

        // Return the list of banks
        return $response['data']['bankList'];
    }

    /**
     * Get authorization headers with a valid access token.
     *
     * @return array
     */
    private function getAuthorizationHeaders(): array
    {
        $accessToken = $this->authenticator->getAccessToken(); // Fetch or refresh the token

        return [
            'Authorization' => "Bearer $accessToken",
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

        /**
     * Safely get a value from a nested array response.
     *
     * @param array $array The API response array
     * @param string $key The key you want, e.g., 'responseCode' or 'data.availableBalance'
     * @param mixed $default What to return if key does not exist
     * @return mixed
     */
    public function getFromResponse(array $array, string $key, $default = null)
    {
        $keys = explode('.', $key);

        foreach ($keys as $innerKey) {
            if (!is_array($array) || !array_key_exists($innerKey, $array)) {
                return $default;
            }
            $array = $array[$innerKey];
        }

        return $array;
    }

}
