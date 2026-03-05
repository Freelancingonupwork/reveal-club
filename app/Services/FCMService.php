<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Exception;
use Illuminate\Support\Facades\Log;

class FCMService {

    protected $credentialsPath, $messaging;

    public function __construct() 
    {
        Log::info("Attempting to send push notification");

        // Path to the firebase credentials file
        $this->credentialsPath = storage_path('app/firebase/firebase_credentials.json');

        if (!file_exists($this->credentialsPath)) {
            Log::error("Firebase credentials file not found at: " . $this->credentialsPath);
            $this->messaging = null;
            return;
        }

        try {
            $this->messaging = (new Factory)->withServiceAccount($this->credentialsPath)->createMessaging();
        } catch (Exception $e) {
            Log::error("Firebase initialization error: " . $e->getMessage());
            $this->messaging = null;
        }
    }
    
    public function communityPostNotification($title, $body, $token) {
        if (!$this->messaging) {
            return ['error' => 'Firebase messaging is not available.'];
        }
        
        try {
            // Create the notification
            $notification = Notification::create($title, $body);

            // Create the cloud message for token
            $message = CloudMessage::new($token)->withNotification($notification);
            
            // Send the multicast message (message, registration token)
            Log::info("Sending notification to token: " . $token);
            $response = $this->messaging->sendMulticast($message, [$token]);
            
            // Handle the response (MulticastSendReport object)
            if ($response->hasFailures()) {
                $failures = $response->failures();
                Log::error("Failed to send notification to: " , array($failures));
                return ['error' => 'Notification failed to send.'];
            } else {
                Log::info("Notification sent successfully.");
                return ['success' => 'Notification sent successfully.'];
            }
        } catch (Exception $e) {
            Log::error("Error sending notification: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}