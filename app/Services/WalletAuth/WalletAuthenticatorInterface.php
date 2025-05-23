<?php

namespace App\Services\WalletAuth;

interface WalletAuthenticatorInterface
{
    public function authenticate(): array;
}
