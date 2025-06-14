<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Services\PushNotificationService;
use App\Traits\ApiResponder;

class NotificationController extends Controller
{
    use ApiResponder;

    protected $pushNotificationService;

    public function __construct(PushNotificationService $pushNotificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
    }   

    public function storeToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error(null, $validator->errors(), 422);
        }

        $user = User::find(auth()->id());
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return $this->success($user->fcm_token, 'Token saved successfully', 201);
    }

    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'array'
        ]);

        if ($validator->fails()) {
            return $this->error(null, $validator->errors(), 422);
        }

        $user = User::find($request->user_id);
        $result = $this->pushNotificationService->sendToUser(
            $user,
            $request->title,
            $request->body,
            $request->data ?? []
        );

        if ($result['success']) {
            return $this->success($result, 'Notification sent successfully');
        }

        return $this->error($result, 'Failed to send notification', 500);
    }

    /**
     * Test endpoint to verify push notification functionality
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:single,multiple,topic',
            'topic' => 'required_if:type,topic|string',
            'user_ids' => 'required_if:type,multiple|array',
            'user_ids.*' => 'exists:users,id',
            'user_id' => 'required_if:type,single|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error(null, $validator->errors(), 422);
        }

        $title = 'Test Notification';
        $body = 'This is a test notification from the server';
        $data = [
            'type' => 'test',
            'timestamp' => now()->toIso8601String(),
            'test_data' => 'This is test data'
        ];

        try {
            switch ($request->type) {
                case 'single':
                    $user = User::find($request->user_id);
                    $result = $this->pushNotificationService->sendToUser(
                        $user,
                        $title,
                        $body,
                        $data
                    );
                    break;

                case 'multiple':
                    $users = User::whereIn('id', $request->user_ids)->get();
                    $result = $this->pushNotificationService->sendToUsers(
                        $users->toArray(),
                        $title,
                        $body,
                        $data
                    );
                    break;

                case 'topic':
                    $result = $this->pushNotificationService->sendToTopic(
                        $request->topic,
                        $title,
                        $body,
                        $data
                    );
                    break;

                default:
                    return $this->error(null, 'Invalid notification type', 422);
            }

            if ($result['success']) {
                return $this->success($result, 'Test notification sent successfully');
            }

            return $this->error($result, 'Failed to send test notification', 500);
        } catch (\Exception $e) {
            return $this->error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'Error sending test notification', 500);
        }
    }

    public function index(Request $request)
    {
        // Since we're not storing notifications in the database,
        // we'll return a message indicating that notifications are push-only
        return $this->success([
            'message' => 'Notifications are push-only and not stored in the database',
            'documentation' => [
                'store_token' => 'POST /notifications/token - Store FCM token',
                'send_notification' => 'POST /notifications/send - Send a notification',
                'test_notification' => 'POST /notifications/test - Test notification functionality'
            ]
        ], 'Notification system information');
    }

    public function show($id)
    {
        return $this->error(null, 'Notifications are push-only and not stored in the database', 404);
    }

    public function markAsRead($id)
    {
        return $this->error(null, 'Notifications are push-only and not stored in the database', 404);
    }

    public function markAllAsRead()
    {
        return $this->error(null, 'Notifications are push-only and not stored in the database', 404);
    }

    public function destroy($id)
    {
        return $this->error(null, 'Notifications are push-only and not stored in the database', 404);
    }

    public function statistics()
    {
        // Since we're not storing notifications in the database,
        // we'll return basic statistics about push notification capabilities
        $stats = [
            'system_type' => 'Push-only notifications',
            'service' => 'Firebase Cloud Messaging (FCM)',
            'capabilities' => [
                'single_user_notifications' => true,
                'multiple_user_notifications' => true,
                'topic_notifications' => true,
                'data_payload' => true,
                'notification_payload' => true
            ]
        ];

        return $this->success($stats, 'Notification system statistics');
    }
}