<?php


return [
    'npsb' => [
        'class'         => \App\Services\WalletAuth\NpsbAuthenticator::class,
        'base_url'      => env('WALLET_NPSB_BASE_URL'),
        'username'      => env('WALLET_NPSB_USERNAME'),
        'password'      => env('WALLET_NPSB_PASSWORD'),
        'client_id'     => env('WALLET_NPSB_CLIENT_ID'),
        'client_secret' => env('WALLET_NPSB_CLIENT_SECRET'),
    ],
    // more wallets
];
