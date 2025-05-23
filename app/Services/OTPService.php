<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OTPService {

    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY_MINUTES = 10;


    public function generateOTP(User $user)
    {

        $otp = str_pad((string)random_int(0, pow(10, self::OTP_LENGTH) - 1), self::OTP_LENGTH, '0', STR_PAD_LEFT);

        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        $this->storeOTP($user->id, $otp);

        $curl = curl_init();

        
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.smartsmssolutions.com/io/api/client/v1/smsotp/send/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'token' => env('SMART_SMS_TOKEN'),
            'sender' => 'Muva App',
            'app_name_code' => 5396066507,
            'phone' => $user->phone,
            'otp' => $otp,
            'template_code' => 7153792424,
            'ref_id' => uniqid(),
            )
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response, true);

        if($response['success'] === true){
            return $otp;
        }else{
            return $otp;
        };
    }

    public function verifyOTP(User $user, $otp)
    {
        $storedOTP = Cache::get($this->getCacheKey($user->id));
        return $storedOTP && $storedOTP === $otp && now()->lessThanOrEqualTo($user->otp_expires_at);
    }

    public function invalidateOTP(User $user): void
    {
        Cache::forget($this->getCacheKey($user->id));
    }

    private function storeOTP(int $userId, string $otp): void
    {
        Cache::put(
            $this->getCacheKey($userId),
            $otp,
            now()->addMinutes(self::OTP_EXPIRY_MINUTES)
        );
    }

    private function getCacheKey(int $userId): string
    {
        return "otp_user_{$userId}";
    }
}
