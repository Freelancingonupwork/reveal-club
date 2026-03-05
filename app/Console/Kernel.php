<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected $commands = [
        Commands\ExportUserDataToCSV::class,
        Commands\ProcessCancellationWindows::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('app:send-emails-to-customer-io')->everyThirtyMinutes();
        $schedule->command('export:users-to-csv')->weekly();
        $schedule->command('subscription:remind')->daily();
        $schedule->command('subscription:cancellation-windows')->daily();
        // $schedule->command('app:send-push-notification')->dailyAt('12:00');
        // $schedule->command('app:send-push-notification')->dailyAt('18:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
