<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\ApiProvider;
use App\Services\ProviderApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOrderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     *
     * @param ApiProvider $provider
     * @param array $apiOrderIds Array of API order IDs to sync
     */
    public function __construct(
        protected ApiProvider $provider,
        protected array $apiOrderIds
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $api = new ProviderApiService($this->provider);
            $response = $api->getStatusBulk($this->apiOrderIds);

            if (!is_array($response)) {
                Log::warning("SyncOrderStatusJob: Invalid response from provider {$this->provider->id}");
                return;
            }

            $orders = Order::whereIn('api_order_id', $this->apiOrderIds)
                ->where('service_id', '!=', 0) // Basic sanity check
                ->get();

            $upserts = [];

            foreach ($orders as $order) {
                $data = $response[$order->api_order_id] ?? null;

                if (!$data) {
                    continue;
                }

                $newStatus = $this->mapStatus($data['status'] ?? '');

                if ($newStatus !== $order->status) {
                    $upserts[] = [
                        'id'         => $order->id,
                        'status'     => $newStatus,
                        'remains'    => $data['remains'] ?? $order->remains,
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($upserts)) {
                Order::upsert($upserts, ['id'], ['status', 'remains', 'updated_at']);
                Log::info("SyncOrderStatusJob: Updated " . count($upserts) . " orders for provider {$this->provider->id}");
            }

        } catch (\Throwable $e) {
            Log::error("SyncOrderStatusJob failed for provider {$this->provider->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Map provider status to internal status.
     */
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
