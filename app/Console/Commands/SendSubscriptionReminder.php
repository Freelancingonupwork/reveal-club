<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UsersSubscriptions;
use App\Services\CustomerIoService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendSubscriptionReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:remind';
    protected $description = 'Send subscription reminder emails to users 7 days before expiration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = UsersSubscriptions::where('end_date', '=', Carbon::now()->addDays(7)->format('Y-m-d'))->get();

        if ($users->isEmpty()) {
            \Log::info("No users found with subscriptions ending in 7 days.");
            return;
        }
   
        foreach ($users as $user) {
            $userData = User::where('id',$user->user_id)->first(); 
            $customerIo = new CustomerIoService();
            $customerIo->sendTransactionalEmail($userData['email'], '11',['name' => $userData['name'],'endDate' => $user->end_date]);
        }
        \Log::info("Subscription reminder emails sent successfully.");
    }
}
