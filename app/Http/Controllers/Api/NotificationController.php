<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Traits\ApiResponder;

class NotificationController extends Controller
{
    use ApiResponder;

    public function storeToken(Request $request){
        $this->validate($request, [
            'fcm_token' => 'required',
        ]);

        User::where('fcm_token', $request->fcm_token)->update(['fcm_token' => '']);

        $user = $request->user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return $this->success(['fcm_token' => $user->fcm_token], 'Token updated successfully', 200);

    }

    // public function sendPushNotification(Request $request)
    // {
    //     $expoPushToken = $request->input('expoPushToken');
    //     $title = $request->input('title', 'Default Title');
    //     $body = $request->input('body', 'Default Body');

    //     $message = [
    //         'to' => $expoPushToken,
    //         'sound' => 'default',
    //         'title' => $title,
    //         'body' => $body,
    //         'data' => ['someData' => 'goes here'],
    //     ];

    //     $response = Http::post('https://exp.host/--/api/v2/push/send', $message);

    //     return response()->json([
    //         'success' => $response->successful(),
    //         'message' => $response->json(),
    //     ]);
    // }
}
