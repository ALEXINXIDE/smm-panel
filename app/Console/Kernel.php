<?php

namespace App\Console;

use App\Console\Commands\SyncOrderStatus;
use App\Console\Commands\PruneProviderLogs;
use App\Console\Commands\QueueHealthCheck;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Sync pending orders with provider APIs every 5 minutes
        $schedule->command(SyncOrderStatus::class)
            ->everyFiveMinutes()
            ->withoutOverlapping(10)
            ->runInBackground();

        // Delete provider API logs older than 90 days
        $schedule->command('logs:prune-provider-logs --days=90')
            ->daily()
            ->at('03:00')
            ->withoutOverlapping();

        // Prune failed jobs older than 7 days
        $schedule->command('queue:prune-failed --hours=168')
            ->daily()
            ->at('02:00');

        // Alert if failed_jobs count exceeds threshold
        $schedule->command('queue:health-check')
            ->everyFifteenMinutes()
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
