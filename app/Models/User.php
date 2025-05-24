<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Services\PushNotificationService;
use App\Models\WalletTransaction;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $pushNotificationService;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->pushNotificationService = app(PushNotificationService::class);
    }

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

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function getWallet($user_id, $slug)
    {
        $user_id = $user_id ?? auth()->id();

        return $this->wallet()->where('user_id', $user_id)->where('slug', $slug)->firstOrFail();

    }

    public function walletBalance($user_id, $slug){
        return Wallet::where('user_id', $user_id)->where('slug', $slug)->first()->balance;
    }

    public function walletDeposit($user_id, $slug, $amount, $meta): void
    {
        $wallet = Wallet::where('user_id', $user_id)->where('slug', $slug)->first();
        $wallet->balance = $amount + $wallet->balance;
        $wallet->save();

        $transaction = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'type' => 'deposit',
            'reference' => $meta['type'] === 'refund' ? $meta['transaction_id'] : $meta['reference'],
        ]);

        $title = 'Wallet Deposit';
        $body = 'Your wallet has been credited with ' . $amount . ' ' . $slug;
        $data = [
            'type' => 'payment',
            'description' => $meta['description'],
        ];

        $this->pushNotificationService->sendToUser($this, $title, $body, $data);

    }
}
