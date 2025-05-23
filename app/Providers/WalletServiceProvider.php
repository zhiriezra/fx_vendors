<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NpsbWalletApiClient;
use App\Services\WalletAuth\NpsbAuthenticator;
use Illuminate\Support\Facades\Log;

class WalletServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind NpsbAuthenticator
        $this->app->singleton(NpsbAuthenticator::class, function ($app) {
            $config = config('wallet_auth.npsb');
            $slug = 'npsb'; // Example slug

            // Validate that all required keys are present
            foreach ($config as $key => $value) {
                if (empty($value)) {
                    Log::error("[$slug] Missing or invalid configuration key: $key for NpsbAuthenticator.", [
                        'environment' => app()->environment(),
                    ]);
                    throw new \InvalidArgumentException("Missing or invalid configuration key: $key for NpsbAuthenticator.");
                }
            }

            return new NpsbAuthenticator($config, $slug);
        });

        // Bind NpsbWalletApiClient
        $this->app->singleton(NpsbWalletApiClient::class, function ($app) {
            $baseUrl = config('wallet_auth.npsb.base_url');

            // Validate that the base URL is present
            if (empty($baseUrl)) {
                Log::error("[NpsbWalletApiClient] Missing or invalid configuration key: WALLET_NPSB_BASE_URL.", [
                    'environment' => app()->environment(),
                ]);
                throw new \InvalidArgumentException("Missing or invalid configuration key: WALLET_NPSB_BASE_URL for NpsbWalletApiClient.");
            }

            $authenticator = $app->make(NpsbAuthenticator::class);

            return new NpsbWalletApiClient($baseUrl, $authenticator);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
