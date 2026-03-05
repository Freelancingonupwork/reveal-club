<?php

namespace App\Services;

use Intercom\IntercomClient;
use App\Models\User;

class IntercomService
{
    protected $intercom;

    public function __construct()
    {
        $this->intercom = new IntercomClient(config('services.intercom.access_token'), null, ['Intercom-Version' => '2.1']);
        // $admins = $this->intercom->admins->getAdmins();
        // dd($admins);
    }

    /**
     * Handle user registration with Intercom
     */
    public function registerWithIntercom($user, $platform)
    {
        try {   
            
            // Create user in Intercom
            $intercomUser = $this->intercom->contacts->create([
                "email" => $user->email,
                "name" => $user->name,
                "phone" => $user->phone ?? null,
                "created_at" => $user->created_at->timestamp,
                "custom_attributes" => [
                    "platform" => $platform,
                    "user_id" => $user->id
                ]
            ]);

            // Generate hash for mobile SDK
            $hash = $this->generateIntercomHash($user->email, $platform);

            // Update user with Intercom data
            $user->update([
                'intercom_id' => $intercomUser->id,
                'intercom_hash' => $hash
            ]);
            return [
                'intercom_user_id' => $intercomUser->id,
                'intercom_hash' => $hash
            ];

        } catch (\Exception $e) {
            \Log::error('Intercom Error: ' . $e->getMessage());
            return null;
        }
    }

    private function generateIntercomHash($userEmail, $platform)
    {
        $secretKey = $platform === 'ios' 
            ? config('services.intercom.ios_key')
            : config('services.intercom.android_key');
            
        return hash_hmac('sha256', $userEmail, $secretKey);
    }


    /**
     * Update user in Intercom
     */
    public function updateUser(User $user)
    {
        return $this->intercom->users->update([
            "user_id" => $user->intercom_id,
            "email" => $user->email,
            "name" => $user->name,
            // Add any other attributes you want to update
        ]);
    }

    /**
     * Log custom event
     */
    public function logEvent(User $user, string $eventName, array $metadata = [])
    {
        return $this->intercom->events->create([
            "user_id" => $user->intercom_id,
            "event_name" => $eventName,
            "created_at" => time(),
            "metadata" => $metadata
        ]);
    }
}