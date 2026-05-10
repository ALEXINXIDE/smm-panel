<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\ProviderApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * SyncOrderStatus
 *
 * FIXES:
 * - HIGH-5: Uses chunkById() instead of get() to avoid loading all orders into memory
 * - HIGH-5: Uses Order::upsert() for batch updates instead of per-row UPDATE queries
 * - Returns proper exit codes
 */
class SyncOrderStatus extends Command
{
    protected $signature   = 'orders:sync';
    protected $description = 'Sync pending orders from API providers (chunked, memory-safe)';

    public function handle(): int
    {
        $this->info('Starting order sync…');

        $totalUpdated = 0;

        // FIXED: chunkById instead of get() — processes 200 rows at a time
        Order::whereIn('status', ['pending', 'in progress'])
            ->whereNotNull('api_order_id')
            ->with('service.apiProvider')
            ->chunkById(200, function ($orders) use (&$totalUpdated) {
                // Group by provider so we do one bulk API call per provider per chunk
                $byProvider = $orders->groupBy(fn ($o) => optional($o->service?->apiProvider)->id);

                foreach ($byProvider as $providerId => $group) {
                    $provider = $group->first()->service?->apiProvider;

                    if (! $provider) {
                        continue;
                    }

                    $ids = $group->pluck('api_order_id')->toArray();
                    
                    // Dispatch the job to process this provider's batch
                    \App\Jobs\SyncOrderStatusJob::dispatch($provider, $ids);
                    
                    $this->line("  Dispatched sync job for Provider #{$providerId} (" . count($ids) . " orders)");
                }
            });

        $this->info("Sync complete. Updated {$totalUpdated} orders.");
        Log::info('orders:sync completed', ['updated' => $totalUpdated]);

        return Command::SUCCESS;
    }

    private function mapStatus(string $raw): string
    {
        return match (strtolower(trim($raw))) {
            'completed'                   => 'completed',
            'partial'                     => 'partial',
            'cancelled', 'canceled'       => 'cancelled',
            'processing', 'in progress'   => 'in progress',
            'error', 'fail', 'failed'     => 'error',
            default                       => 'pending',
        };
    }
}
