<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UsersSubscriptions;
use App\Services\CustomerIoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessCancellationWindows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:cancellation-windows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process yearly-monthly subscription cancellation windows and auto-extend locks when needed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $customerIo = new CustomerIoService();

        // 1) Send notification to users whose lockDate is today
        $toNotify = UsersSubscriptions::where('is_yearly_commitment', 1)
            // ->where('is_cancellation_locked', 0)
            ->whereDate('lockDate', $today->toDateString())
            ->get();
        $login_url = route('user-login');
        foreach ($toNotify as $sub) {
            try {
                $user = $sub->user;
                if (!$user) continue;
                $windowStart = Carbon::parse($sub->lockDate);
                $windowEnd = $windowStart->copy()->addMonth();
                $customerIo->sendTransactionalEmail($user->email, '12', ['start_date' => $windowStart->toDateString(), 'end_date' => $windowEnd->toDateString(),'login_url' => $login_url]);
                Log::info('Scheduled job - sent cancellation window email', ['user' => $user->email]);
            } catch (\Exception $e) {
                Log::error('Scheduled job error sending cancellation window: ' . $e->getMessage());
            }
        }

        // 2) Auto-extend locks for subscriptions whose cancellation window has expired and they did not cancel
        $expired = UsersSubscriptions::where('package_type', 'yearly_monthly_plan')
            ->where('is_cancellation_locked', 1)
            ->whereNotNull('lockDate')
            ->get();

        foreach ($expired as $sub) {
            try {
                $windowStart = Carbon::parse($sub->lockDate);
                $windowEnd = $windowStart->copy()->addMonth();
                $now = Carbon::now();
                // If windowEnd has passed and subscription is still active (not canceled), extend
                if ($now->gt($windowEnd) && $sub->status !== 'canceled') {
                    $sub->lockDate = $sub->lockDate ? Carbon::parse($sub->lockDate)->addMonths(12)->toDateString() : $now->addMonths(12)->toDateString();
                    $sub->subscription_year_cycle = ($sub->subscription_year_cycle ?? 0) + 1;
                    $sub->save();
                    // Optionally notify user about auto-extension
                    $user = $sub->user;
                    if ($user) {
                        $customerIo->sendTransactionalEmail($user->email, 'cancellation_window_extended', ['new_lock_until' => $sub->lockDate]);
                    }
                    Log::info('Scheduled job - auto-extended lock for subscription', ['subscription_id' => $sub->subscription_id]);
                }
            } catch (\Exception $e) {
                Log::error('Scheduled job error auto-extending lock: ' . $e->getMessage());
            }
        }

        return 0;
    }
}
