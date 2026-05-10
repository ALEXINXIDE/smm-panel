<?php

namespace App\Console\Commands;

// ============================================================================
// PruneProviderLogs — Scheduled Log Rotation
// ============================================================================
// Provider logs accumulate rapidly (every API call = 1 row).
// A panel with 100 active orders synced every 5 minutes = 100*288 = 28,800
// rows per day. Without pruning, this table grows unbounded.
//
// Retention policy: 90 days by default (configurable via --days option).
// This gives enough history for debugging and dispute resolution.
// ============================================================================

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PruneProviderLogs extends Command
{
    protected $signature = 'logs:prune-provider-logs {--days=90 : Delete logs older than this many days}';
    protected $description = 'Prune old provider API logs to control database growth';

    public function handle(): int
    {
        $days      = (int) $this->option('days');
        $cutoff    = now()->subDays($days);

        $this->info("Pruning provider logs older than {$days} days (before {$cutoff})...");

        try {
            // Delete in batches of 1000 to avoid long lock durations
            $totalDeleted = 0;

            do {
                $deleted = DB::table('provider_logs')
                    ->where('created_at', '<', $cutoff)
                    ->limit(1000)
                    ->delete();
                $totalDeleted += $deleted;
            } while ($deleted > 0);

            $this->info("Pruned {$totalDeleted} provider log rows.");
            Log::info("PruneProviderLogs: deleted {$totalDeleted} rows older than {$days} days.");

            // Also prune activity logs older than 365 days
            $activityCutoff = now()->subDays(365);
            $activityDeleted = 0;

            do {
                $deleted = DB::table('activity_logs')
                    ->where('created_at', '<', $activityCutoff)
                    ->limit(1000)
                    ->delete();
                $activityDeleted += $deleted;
            } while ($deleted > 0);

            if ($activityDeleted > 0) {
                $this->info("Also pruned {$activityDeleted} activity log rows older than 365 days.");
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Log pruning failed: ' . $e->getMessage());
            Log::error('PruneProviderLogs failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
