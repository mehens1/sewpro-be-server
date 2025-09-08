<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FcmService
{
    protected string $serverKey;

    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key') ?: env('FCM_SERVER_KEY');
    }

    public function sendNotification(array $tokens, string $title, string $body, array $data = []): array
    {
        if (empty($tokens)) {
            return [];
        }

        $payload = [
            'registration_ids' => array_values($tokens),
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
            'priority' => 'high',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);

        return $response->json() ?? [];
    }
}
