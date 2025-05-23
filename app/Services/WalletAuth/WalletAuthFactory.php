<?php

namespace App\Services\WalletAuth;

use Illuminate\Support\Facades\Config;

class WalletAuthFactory
{
    public static function make(string $slug): WalletAuthenticatorInterface
    {
        $config = Config::get("wallet_auth.$slug");

        if (empty($config)) {
            throw new \InvalidArgumentException("Configuration not found for wallet: $slug");
        }

        $class = $config['class'];

        return new $class($config, $slug); // Pass the slug explicitly
    }
}
