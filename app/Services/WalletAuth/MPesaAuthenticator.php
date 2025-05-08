<?php

namespace App\Services\WalletAuth;

use App\Traits\HandlesConfiguration;
use App\Traits\MakesHttpRequests;

class MPesaAuthenticator implements WalletAuthenticatorInterface
{
    use HandlesConfiguration, MakesHttpRequests;

    public function authenticate(): array
    {
        //M-Pesa implementation here

        $url = $this->getConfig('base_url') . '/login';

        return $this->post($url, [
            'username' => $this->getConfig('username'),
            'password' => $this->getConfig('password'),
        ]);
    }
}
