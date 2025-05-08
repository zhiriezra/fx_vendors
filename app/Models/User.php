<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements Wallet
{
    use HasApiTokens, HasFactory, Notifiable, HasWallet;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function usertype(){
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    // public function business(){
    //     return $this->hasOne(BusinessInfo::class);
    // }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

     /*     public function wallets(){
        return $this->hasOne(ModelsWallet::class);
    } */

   /*     public function wallets(){
        return $this->hasOne(ModelsWallet::class);
    } */

    // If you want to manually define, it should be like this:

    public function wallets()
    {
        return $this->morphMany(\Bavix\Wallet\Models\Wallet::class, 'holder');
    }

    public function getWallet($user_id, $slug)
    {
        $user_id = $user_id ?? auth()->id();

        return $this->wallets()->where('holder_id', $user_id)->where('slug', $slug)->firstOrFail();

        /* return ModelsWallet::where('holder_id', $user_id)
            ->where('holder_type', self::class)
                ->where('slug', $slug)
                    ->firstOrFail(); */
    }

    public function walletDeposit($user_id, $slug, $amount)
    {
        return $this->getWallet($user_id, $slug)->depositFloat($amount);
    }

    public function walletWithdraw($user_id, $slug, $amount)
    {
        return $this->getWallet($user_id, $slug)->withdrawFloat($amount);
    }

    public function walletBalance($user_id, $slug)
    {
        return $this->getWallet($user_id, $slug)->balanceFloatNum;
    }

}
