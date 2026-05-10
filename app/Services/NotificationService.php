<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification to specific user IDs via OneSignal
     *
     * @param array $userIds
     * @param string $title
     * @param string $message
     * @param array $data
     * @return bool
     */
    public static function sendToUsers(array $userIds, string $title, string $message, array $data = [])
    {
        if (empty($userIds)) {
            return false;
        }

        $appId = env('ONESIGNAL_APP_ID', '85810017-d9f0-4f0c-9d80-6d94df461f61');
        $restApiKey = env('ONESIGNAL_REST_API_KEY', '');

        if (empty($restApiKey)) {
            Log::warning('OneSignal REST API Key is not set. Cannot send notification.');
            return false;
        }

        // Convert all IDs to string as OneSignal expects strings for aliases
        $stringIds = array_map(fn($id) => (string)$id, $userIds);

        $payload = [
            'app_id' => $appId,
            'include_aliases' => ['external_id' => array_values($stringIds)],
            'target_channel' => 'push',
            'contents' => [
                'en' => $message,
                'id' => $message
            ],
            'headings' => [
                'en' => $title,
                'id' => $title
            ]
        ];

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $restApiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://onesignal.com/api/v1/notifications', $payload);

            if ($response->successful()) {
                return true;
            } else {
                Log::error('OneSignal Error: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('OneSignal Exception: ' . $e->getMessage());
            return false;
        }
    }
}
