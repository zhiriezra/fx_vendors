<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'provider',
        'description',
    ];

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'wallet_type_countries');
    }

    public function wallets(){
        return $this->hasMany(Wallet::class);
    }

}
