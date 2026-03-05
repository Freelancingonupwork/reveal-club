<?php

namespace App\Console\Commands;

use App\Models\LessonsPlanner;
use App\Models\User;
use App\Traits\PushNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-push-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send push notifications to users who haven\'t logged their lessons for today';

    /**
     * Execute the console command.
     */

    use PushNotification;
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $allUsersIds = User::all()->pluck('id')->toArray();

        $UserLoggedToday = LessonsPlanner::where([
            ['date', now()->format('Y-m-d')],
            ['is_logged', '1'],
        ])->pluck('user_id')->toArray();
        
        $usersNotLoggedToday = array_diff($allUsersIds, $UserLoggedToday);
        
        $allDevicesTokens = User::whereIn('id', $usersNotLoggedToday)->pluck('device_token')->toArray();
        $allDevicesTokens = array_filter($allDevicesTokens); // Remove any null or empty tokens

        $title = 'Complete your daily lessons';
        $body = 'You have not logged your lessons for today. Please complete your daily lessons.';
    
        $deviceTokensChunks = array_chunk($allDevicesTokens, 400);
    
        // Send notification to users in chunks
        foreach ($deviceTokensChunks as $chunk) {
            if (count($chunk) > 0) {
                $response = $this->sendPushNotification($title, $body, $chunk);
    
                if (isset($response['error'])) {
                    $this->error('Error sending notification: ' . $response['error']);
                } else {
                    $this->info('Notifications sent to batch: ' . implode(', ', $chunk));
                    Log::info('Notifications sent to users: ' . implode(', ', $chunk));
                }
            }
        }
    
        if (empty($allDevicesTokens)) {
            $this->info('No users found to send notifications.');
        }
    }
}
