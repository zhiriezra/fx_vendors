<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    public function walletTypes()
    {
        return $this->belongsToMany(WalletType::class, 'wallet_type_countries');
    }

}

