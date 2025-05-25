<?php

namespace App\Factories;

use InvalidArgumentException;

class WalletProviderFactory
{
    public function make(string $provider): object
    {
        // Map wallet providers to their respective service classes
        $providers = [
            'Npsb'   => \App\Services\NpsbWalletService::class,
            //'mpesa'  => \App\Services\MpesaWalletService::class,
            // Add more providers here
        ];

        if (!isset($providers[$provider])) {
            throw new InvalidArgumentException("Unsupported wallet provider: $provider");
        }

        // Instantiate and return the wallet service
        return app($providers[$provider]);
    }
}
