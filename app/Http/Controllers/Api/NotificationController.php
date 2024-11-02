<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{
    public function storeToken(Request $request){
        $this->validate($request, [
            'fcm_token' => 'required',
        ]);

        $user = $request->user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json(['status' => true, 'message' => 'success', 'data' => ['fcm_token' => $user->fcm_token]], 200);

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
