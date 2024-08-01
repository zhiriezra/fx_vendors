<?php

namespace App\Services;

use App\Models\User;

class OTPService {

    public function generateOTP(User $user){

        $otp = random_int(100000, 999999);

        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();


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
            'token' => 'uzAkF1lx0SjXC6FsdWmCWRN4g7dJTdLwxHD1u7WiZUzWtk2HQd',
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
        };
    }


    public function verifyOTP(User $user, $otp)
    {
        return $user->otp == $otp && now()->lessThanOrEqualTo($user->otp_expires_at);
    }
}
