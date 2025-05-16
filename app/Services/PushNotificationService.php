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
            $projectId = env('FIREBASE_PROJECT_ID');
            if (empty($projectId)) {
                throw new \Exception('FIREBASE_PROJECT_ID is not set in environment variables');
            }

            $credentials = [
                'type' => 'service_account',
                'project_id' => $projectId,
                'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),
                'private_key' => str_replace('\n', "\n", env('FIREBASE_PRIVATE_KEY')),
                'client_email' => env('FIREBASE_CLIENT_EMAIL'),
                'client_id' => env('FIREBASE_CLIENT_ID'),
                'auth_uri' => env('FIREBASE_AUTH_URI', 'https://accounts.google.com/o/oauth2/auth'),
                'token_uri' => env('FIREBASE_TOKEN_URI', 'https://oauth2.googleapis.com/token'),
                'auth_provider_x509_cert_url' => env('FIREBASE_AUTH_PROVIDER_CERT_URL', 'https://www.googleapis.com/oauth2/v1/certs'),
                'client_x509_cert_url' => env('FIREBASE_CLIENT_CERT_URL'),
                'universe_domain' => env('FIREBASE_UNIVERSE_DOMAIN', 'googleapis.com')
            ];

            // Validate required credentials
            $requiredFields = ['private_key_id', 'private_key', 'client_email', 'client_id'];
            foreach ($requiredFields as $field) {
                if (empty($credentials[$field])) {
                    throw new \Exception("FIREBASE_{$field} is not set in environment variables");
                }
            }

            Log::info('Initializing Firebase with project ID: ' . $projectId);
            
            $this->messaging = (new Factory)
                ->withServiceAccount($credentials)
                ->createMessaging();
                
            Log::info('Firebase initialized successfully');
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