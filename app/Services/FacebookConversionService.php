<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FacebookConversionService
{
    private string $pixelId;
    private string $accessToken;
    private string $testEventCode;

    public function __construct()
    {
        $this->pixelId = config('services.facebook.pixel_id');
        $this->accessToken = config('services.facebook.access_token');
        $this->testEventCode = config('services.facebook.test_event_code');
    }

    public function sendLeadEvent(array $leadData, string $eventId): void
    {
        $eventData = [
            'data' => [
                [
                    'event_name' => 'Lead',
                    'event_time' => time(),
                    'event_id' => $eventId,
                    'action_source' => 'website',
                    'user_data' => $this->prepareUserData($leadData),
                ]
            ],
            'access_token' => $this->accessToken,
        ];

        if ($this->testEventCode) {
            $eventData['test_event_code'] = $this->testEventCode;
        }

        Log::info('Facebook CAPI - Lead Event', [
            'event_id' => $eventId,
            'payload' => $eventData,
            'url' => "https://graph.facebook.com/v21.0/{$this->pixelId}/events"
        ]);

        $response = Http::post(
            "https://graph.facebook.com/v21.0/{$this->pixelId}/events",
        $eventData
        );

        Log::info('Facebook CAPI - Response', [
            'event_id' => $eventId,
            'status'   => $response->status(),
            'body'     => $response->json(),
        ]);
    }

    private function prepareUserData(array $leadData): array
    {
        $userData = [];

        if (!empty($leadData['email'])) {
            $userData['em'] = hash('sha256', strtolower(trim($leadData['email'])));
        }

        if (!empty($leadData['phone_number'])) {
            $phone = preg_replace('/[^0-9]/', '', $leadData['phone_number']);
            $userData['ph'] = hash('sha256', $phone);
        }

        if (!empty($leadData['first_name'])) {
            $userData['fn'] = hash('sha256', strtolower(trim($leadData['first_name'])));
        }

        if (!empty($leadData['last_name'])) {
            $userData['ln'] = hash('sha256', strtolower(trim($leadData['last_name'])));
        }

        if (!empty($leadData['ip_address'])) {
            $userData['client_ip_address'] = $leadData['ip_address'];
        }

        if (!empty($leadData['user_agent'])) {
            $userData['client_user_agent'] = $leadData['user_agent'];
        }

        return $userData;
    }
}
