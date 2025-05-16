<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\MessagingException;

class PushNotificationService
{
    protected $messaging;

    public function __construct()
    {
        try {
            $credentials = config('firebase.projects.app.credentials.file');
            
            if (empty($credentials)) {
                throw new \Exception('Firebase credentials file path is not configured');
            }

            if (!file_exists($credentials)) {
                throw new \Exception('Firebase credentials file not found at: ' . $credentials);
            }

            $this->messaging = (new Factory)
                ->withServiceAccount($credentials)
                ->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send notification to a single user
     *
     * @param User $user
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array|bool
     */
    public function sendToUser(User $user, string $title, string $body, array $data = [])
    {
        if (!$user->fcm_token) {
            Log::warning("No FCM token found for user ID: {$user->id}");
            return [
                'success' => false,
                'message' => 'No FCM token found for user',
                'user_id' => $user->id
            ];
        }

        try {
            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $response = $this->messaging->send($message);
            
            return [
                'success' => true,
                'message' => 'Notification sent successfully',
                'response' => $response,
                'user_id' => $user->id
            ];
        } catch (MessagingException $e) {
            Log::error('FCM notification error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'error_code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'user_id' => $user->id
            ];
        } catch (\Exception $e) {
            Log::error('FCM notification error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ];
        }
    }

    /**
     * Send notification to multiple users
     *
     * @param array $users
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public function sendToUsers(array $users, string $title, string $body, array $data = [])
    {
        $tokens = collect($users)
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            Log::warning("No FCM tokens found for the provided users");
            return [
                'success' => false,
                'message' => 'No FCM tokens found for the provided users'
            ];
        }

        try {
            $notification = Notification::create($title, $body);
            $messages = [];

            foreach ($tokens as $token) {
                $messages[] = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)
                    ->withData($data);
            }

            $response = $this->messaging->sendAll($messages);
            
            return [
                'success' => true,
                'message' => 'Notifications sent successfully',
                'response' => $response,
                'tokens_count' => count($tokens)
            ];
        } catch (MessagingException $e) {
            Log::error('FCM notification error', [
                'message' => $e->getMessage(),
                'tokens_count' => count($tokens),
                'error_code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'tokens_count' => count($tokens)
            ];
        } catch (\Exception $e) {
            Log::error('FCM notification error', [
                'message' => $e->getMessage(),
                'tokens_count' => count($tokens)
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'tokens_count' => count($tokens)
            ];
        }
    }

    /**
     * Send notification to a topic
     *
     * @param string $topic
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = [])
    {
        try {
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $response = $this->messaging->send($message);
            
            return [
                'success' => true,
                'message' => 'Topic notification sent successfully',
                'response' => $response,
                'topic' => $topic
            ];
        } catch (MessagingException $e) {
            Log::error('FCM topic notification error', [
                'message' => $e->getMessage(),
                'topic' => $topic,
                'error_code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'topic' => $topic
            ];
        } catch (\Exception $e) {
            Log::error('FCM topic notification error', [
                'message' => $e->getMessage(),
                'topic' => $topic
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'topic' => $topic
            ];
        }
    }

    /**
     * Subscribe a user to a topic
     *
     * @param User $user
     * @param string $topic
     * @return array
     */
    public function subscribeToTopic(User $user, string $topic)
    {
        if (!$user->fcm_token) {
            Log::warning("No FCM token found for user ID: {$user->id}");
            return [
                'success' => false,
                'message' => 'No FCM token found for user',
                'user_id' => $user->id
            ];
        }

        try {
            $response = $this->messaging->subscribeToTopic($topic, [$user->fcm_token]);
            
            return [
                'success' => true,
                'message' => 'Successfully subscribed to topic',
                'response' => $response,
                'user_id' => $user->id,
                'topic' => $topic
            ];
        } catch (MessagingException $e) {
            Log::error('FCM topic subscription error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'topic' => $topic,
                'error_code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'user_id' => $user->id,
                'topic' => $topic
            ];
        } catch (\Exception $e) {
            Log::error('FCM topic subscription error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'topic' => $topic
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'topic' => $topic
            ];
        }
    }

    /**
     * Unsubscribe a user from a topic
     *
     * @param User $user
     * @param string $topic
     * @return array
     */
    public function unsubscribeFromTopic(User $user, string $topic)
    {
        if (!$user->fcm_token) {
            Log::warning("No FCM token found for user ID: {$user->id}");
            return [
                'success' => false,
                'message' => 'No FCM token found for user',
                'user_id' => $user->id
            ];
        }

        try {
            $response = $this->messaging->unsubscribeFromTopic($topic, [$user->fcm_token]);
            
            return [
                'success' => true,
                'message' => 'Successfully unsubscribed from topic',
                'response' => $response,
                'user_id' => $user->id,
                'topic' => $topic
            ];
        } catch (MessagingException $e) {
            Log::error('FCM topic unsubscription error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'topic' => $topic,
                'error_code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'user_id' => $user->id,
                'topic' => $topic
            ];
        } catch (\Exception $e) {
            Log::error('FCM topic unsubscription error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'topic' => $topic
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'topic' => $topic
            ];
        }
    }
} 