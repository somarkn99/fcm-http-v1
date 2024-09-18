<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

class FcmService
{
    protected $client;
    protected $credentialsPath;
    protected $projectId;

    public function __construct()
    {
        $this->credentialsPath = config('services.fcm.credentialsPath');
        $this->projectId = config('services.fcm.project_id');
    }

    public function sendNotification($to, $title, $body)
    {
        // Load Service Account credentials from JSON file
        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $this->credentialsPath
        );

        // Get an OAuth 2.0 token
        $authToken = $credentials->fetchAuthToken()['access_token'];

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        try {
            $response = Http::withToken($authToken)->post($url, [
                'message' => [
                        'token' => $to,
                        'data' => [
                            'title' => $title,
                            'body' => $body,
                            'click_action' => "FLUTTER_NOTIFICATION_CLICK"
                        ],
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'android' => [
                            'priority' => "high",
                        ],
                        'apns' => [
                            'headers' => [
                                'apns-priority' => '10',
                            ],
                            'payload' => [
                                'aps' => [
                                    'content-available' => 1,
                                    'badge' => 5,
                                    'priority' => "high",
                                ]
                            ]
                        ]
                    ]
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }
}
