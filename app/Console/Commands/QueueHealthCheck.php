<?php

namespace App\Console\Commands;

// ============================================================================
// QueueHealthCheck — Monitor Queue Worker Health
// ============================================================================
// Alerts when:
//  - Failed jobs count exceeds threshold (default: 50)
//  - Failed jobs are growing rapidly (new failures in last 15 minutes)
//  - Queue backlog is too large (jobs stuck behind slow processing)
//
// Alerting via: Slack webhook (LOG_SLACK_WEBHOOK_URL) and application log.
// ============================================================================

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class QueueHealthCheck extends Command
{
    protected $signature   = 'queue:health-check';
    protected $description = 'Check queue worker health and alert if issues detected';

    private const FAILED_JOBS_ALERT_THRESHOLD  = 50;
    private const RECENT_FAILURES_WINDOW_MINUTES = 15;
    private const RECENT_FAILURES_THRESHOLD     = 10;

    public function handle(): int
    {
        $issues = [];

        // ── Check 1: Total failed jobs ─────────────────────────────────────
        $totalFailed = DB::table('failed_jobs')->count();

        if ($totalFailed >= self::FAILED_JOBS_ALERT_THRESHOLD) {
            $issues[] = "⚠️ {$totalFailed} failed jobs in queue (threshold: " . self::FAILED_JOBS_ALERT_THRESHOLD . ")";
        }

        // ── Check 2: Recent failure spike ─────────────────────────────────
        $recentFailures = DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subMinutes(self::RECENT_FAILURES_WINDOW_MINUTES))
            ->count();

        if ($recentFailures >= self::RECENT_FAILURES_THRESHOLD) {
            $issues[] = "🚨 {$recentFailures} jobs failed in the last " . self::RECENT_FAILURES_WINDOW_MINUTES . " minutes";
        }

        // ── Check 3: Pending payment jobs age ─────────────────────────────
        // If payment jobs are stuck in the queue, that's critical
        // (Check jobs table if using database driver; for Redis this requires
        // a more complex check via Redis LLEN)
        if (config('queue.default') === 'database') {
            $stuckPayments = DB::table('jobs')
                ->where('queue', 'payments')
                ->where('created_at', '<', now()->subMinutes(10))
                ->count();

            if ($stuckPayments > 0) {
                $issues[] = "🚨 {$stuckPayments} payment jobs stuck for >10 minutes — are workers running?";
            }
        }

        if (empty($issues)) {
            $this->info('Queue health: OK');
            return Command::SUCCESS;
        }

        // ── Alert ─────────────────────────────────────────────────────────
        $message = "Queue Health Alert:\n" . implode("\n", $issues);

        Log::channel('slack')->critical($message, [
            'total_failed'     => $totalFailed,
            'recent_failures'  => $recentFailures,
        ]);

        $this->error($message);

        return Command::FAILURE;
    }
}
