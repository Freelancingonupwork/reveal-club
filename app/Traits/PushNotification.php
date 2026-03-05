<?php

namespace App\Traits;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Exception;
use Illuminate\Support\Facades\Log;

trait PushNotification
{
    public function sendPushNotification($title, $body, $tokens)
    {
        Log::info("Attempting to send push notification");
    
        // Path to the Firebase credentials file
        $credentialsPath = storage_path('app/firebase/firebase_credentials.json');
        
        // Check if the credentials file exists
        if (!file_exists($credentialsPath)) {
            Log::error("Firebase credentials file not found at: " . $credentialsPath);
            return ['error' => 'Firebase credentials file not found.'];
        }
    
        try {
            // Initialize Firebase Messaging
            $messaging = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->createMessaging();
    
            // Check if messaging is initialized
            if ($messaging === null) {
                Log::error("Firebase messaging service is not initialized.");
                return ['error' => 'Firebase messaging service is not initialized.'];
            } else {
                Log::info("Firebase messaging initialized successfully.");
            }
    
            // Create the notification
            $notification = Notification::create($title, $body);
    
            // Create the CloudMessage for each token
            $messages = [];
            foreach ($tokens as $token) {
                $messages = CloudMessage::new($token)->withNotification($notification);
            }
    
            // Send the multicast message (messages, registration tokens)
            Log::info("Sending notifications to tokens: " . implode(", ", $tokens));
            $response = $messaging->sendMulticast(
                $messages,  // The array of CloudMessage objects
                $tokens     // The array of device tokens
            );
            // dd($response);
            // Handle the response (MulticastSendReport object)
            if ($response->hasFailures()) {
                $failures = $response->failures();
                Log::error("Failed to send notifications to: " , array($failures));
                return ['error' => 'Some notifications failed to send.'];
            } else {
                Log::info("Notifications sent successfully.");
                return ['success' => 'Notifications sent successfully.'];
            }
    
        } catch (Exception $e) {
            Log::error("Error sending notification: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    
}
