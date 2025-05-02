<?php

namespace App\Services\WalletAuth;

use App\Traits\HandlesConfiguration;
use App\Traits\MakesHttpRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NpsbAuthenticator implements WalletAuthenticatorInterface
{
    use HandlesConfiguration, MakesHttpRequests;

    protected string $baseUrl;
    protected string $slug;

    /**
     * Constructor to initialize the authenticator.
     *
     * @param array $config Configuration array containing required keys.
     * @param string $slug Unique identifier for the wallet provider.
     * @throws \InvalidArgumentException If required configuration keys are missing.
     */
    public function __construct(array $config, string $slug)
    {
        // Assign configuration and slug
        $this->config = $config;
        $this->slug = $slug;

        // Validate required configuration keys
        $requiredKeys = ['base_url', 'username', 'password', 'client_id', 'client_secret'];

        foreach ($requiredKeys as $key) {
            if (empty($this->getConfig($key))) {
                Log::error("Missing or invalid configuration key: $key in NpsbAuthenticator.");
                throw new \InvalidArgumentException("The '$key' configuration is required for NpsbAuthenticator.");
            }
        }

        // Set the base URL
        $this->baseUrl = $this->getConfig('base_url');
    }

    /**
     * Authenticate with the wallet provider and retrieve an access token.
     *
     * @return array Authentication response containing access token and expiration time.
     * @throws \Exception If authentication fails or the response is invalid.
     */
    public function authenticate(): array
    {
        $url = $this->baseUrl . '/authenticate';

        // Make the POST request to authenticate
        $response = $this->post($url, [
            'username'      => $this->getConfig('username'),
            'password'      => $this->getConfig('password'),
            'clientId'      => $this->getConfig('client_id'),
            'clientSecret'  => $this->getConfig('client_secret'),
        ]);

        // Ensure the request was successful
        if (!$response->successful()) {
            Log::error('NPSB Authentication failed', ['response' => $response->body()]);
            throw new \Exception('NPSB authentication failed: ' . $response->body());
        }

        // Parse the JSON response
        $data = $response->json();

        // Validate the response structure
        if (!isset($data['accessToken'], $data['expiresIn'])) {
            Log::error('Invalid authentication response', ['response' => $data]);
            throw new \Exception('Invalid authentication response: Missing accessToken or expiresIn.');
        }

        // Cache the access token with a 1-minute buffer
        Cache::put($this->getCacheKey(), $data['accessToken'], now()->addSeconds($data['expiresIn'] - 60));

        return $data;
    }

    /**
     * Retrieve the cached access token or re-authenticate if it has expired.
     *
     * @return string The valid access token.
     * @throws \Exception If unable to retrieve or generate a valid access token.
     */
    public function getAccessToken(): string
    {
        $cacheKey = $this->getCacheKey();

        // Check if the token exists in the cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Token expired or missing, re-authenticate
        $data = $this->authenticate();

        return $data['accessToken'];
    }

    /**
     * Generate the cache key for storing the access token.
     *
     * @return string The cache key.
     */
    protected function getCacheKey(): string
    {
        return "wallets.{$this->slug}.token";
    }
}
