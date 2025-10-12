<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Services\PushNotificationService;
use App\Models\WalletTransaction;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\FilamentUser;


class User extends Authenticatable implements FilamentUser, HasName
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $pushNotificationService;

    public function getFilamentName(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // Allow access to vendors (user_type_id = 2)
        // Also check if user is active
        return in_array($this->user_type_id, [2]);
    }

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
        return $this->hasOne(Vendor::class, 'user_id');
    }

    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
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

    public function generateTwoFactorCode(){
        
        $otp = rand(10000, 99999); // Generate a 5-digit OTP
        // Store OTP in the database with expiration time
        $this->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(5) // OTP expires after 5 minutes
        ]);

        return $otp;
    }

    /**
     * Get the user's full name for Filament authentication
     */

}
