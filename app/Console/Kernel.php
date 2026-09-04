<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Auto monthly fees OFF by default (ACADEMY_MONTHLY_FEE_AUTO_GENERATE=false).
        // Enable in .env only if you want one invoice per student per month via cron.
        if (config('academy.monthly_fee_auto_generate', false)) {
            $schedule->command('academy:generate-monthly-fees')->dailyAt('01:15');
        }

        $schedule->command('academy:send-fee-reminders')->dailyAt('09:00');
        $schedule->command('academy:backup')->dailyAt('02:00');
        $schedule->command('academy:backup')->weeklyOn(0, '03:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
