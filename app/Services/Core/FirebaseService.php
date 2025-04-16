<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        // $notification = Notification::create($title, $body);
        // $message = CloudMessage::withTarget('token', $token)
        // ->withNotification($notification)
        // ->withData($data);

        $message = CloudMessage::fromArray([
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);

        try {
            $response = $this->messaging->send($message);
            Log::info('Cloud Message sent successfully', [
                'token' => $token,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Cloud Message failed to send', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sendMultipleNotification(array $tokens, $title, $body, $data = [])
    {
        $notification = Notification::create($title, $body);

        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($data);

        $report = $this->messaging->sendMulticast($message, $tokens);

        return [
            'success_count' => $report->successes()->count(),
            'failure_count' => $report->failures()->count(),
            'failures' => $report->failures(),
        ];
    }
}