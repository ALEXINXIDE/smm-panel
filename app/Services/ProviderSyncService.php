<?php

namespace App\Services;

use App\Models\ApiProvider;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ProviderSyncService
 *
 * Uses bulk upsert instead of per-row updateOrCreate to prevent
 * timeouts on providers with hundreds or thousands of services.
 */
class ProviderSyncService
{
    /**
     * Sync all active providers and return total services synced.
     */
    public function syncAll(): int
    {
        $total = 0;

        foreach (ApiProvider::where('status', 'active')->get() as $provider) {
            try {
                $total += $this->syncProvider($provider);
            } catch (\Throwable $e) {
                Log::error("ProviderSyncService::syncAll failed for provider {$provider->id}: " . $e->getMessage());
            }
        }

        return $total;
    }

    /**
     * Sync a single provider and return number of services synced.
     */
    public function syncProvider(ApiProvider $provider): int
    {
        $api      = new ProviderApiService($provider);
        $services = $api->getServices();

        if (! is_array($services) || empty($services)) {
            throw new \RuntimeException("Provider {$provider->name} returned no services.");
        }

        // Step 1 — Collect all unique category names and bulk insert them
        $categoryNames = collect($services)
            ->pluck('category')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($categoryNames)) {
            $categoryNames = ['General'];
        }

        // Insert missing categories in one query
        $existingCategories = Category::whereIn('name', $categoryNames)
            ->pluck('id', 'name');

        $missingCategories = collect($categoryNames)
            ->filter(fn ($n) => ! $existingCategories->has($n))
            ->map(fn ($n) => [
                'name'       => $n,
                'status'     => 'active',
                'icon'       => 'list_alt',
                'color'      => '#adc6ff',
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->toArray();

        if (! empty($missingCategories)) {
            Category::insert($missingCategories);
        }

        // Reload all categories now they all exist
        $categoryMap = Category::whereIn('name', $categoryNames)
            ->pluck('id', 'name');

        // Step 2 — Build upsert rows in memory
        $now  = now()->toDateTimeString();
        $rows = [];

        foreach ($services as $svc) {
            $catName    = $svc['category'] ?? 'General';
            $categoryId = $categoryMap->get($catName) ?? $categoryMap->first();

            if (! $categoryId) {
                continue;
            }

            $rows[] = [
                'api_provider_id' => $provider->id,
                'api_service_id'  => (string) ($svc['service'] ?? $svc['id'] ?? ''),
                'category_id'     => $categoryId,
                'name'            => mb_substr($svc['name'] ?? 'Unnamed Service', 0, 255),
                'rate'            => round(
                    ($svc['rate'] ?? 0) * (1 + ($provider->percentage_increase / 100)),
                    6
                ),
                'min'        => (int) ($svc['min'] ?? 10),
                'max'        => (int) ($svc['max'] ?? 100000),
                'status'     => 'active',
                'type'       => 'api',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($rows)) {
            return 0;
        }

        // Step 3 — Bulk upsert in chunks of 200 to avoid query size limits
        $chunks = array_chunk($rows, 200);

        DB::transaction(function () use ($chunks) {
            foreach ($chunks as $chunk) {
                Service::upsert(
                    $chunk,
                    ['api_provider_id', 'api_service_id'], // unique keys
                    ['name', 'category_id', 'rate', 'min', 'max', 'status', 'updated_at'] // update these
                );
            }
        });

        $synced = count($rows);
        Log::info("Provider {$provider->name} synced {$synced} services.");

        return $synced;
    }
}