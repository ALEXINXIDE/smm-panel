<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ProviderApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff()
    {
        return [30, 60, 120, 300, 600]; // Exponential backoff in seconds
    }

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Order $order)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Don't re-submit if already has API ID
        if ($this->order->api_order_id) {
            return;
        }

        $service = $this->order->service;
        
        if (!$service || !$service->api_provider_id || !$service->api_service_id) {
            Log::warning("ProcessOrderJob: Order #{$this->order->id} has no valid API provider or service ID.");
            return;
        }

        try {
            $api = new ProviderApiService($service->apiProvider);
            $result = $api->addOrder(
                (int) $service->api_service_id,
                $this->order->link,
                $this->order->quantity
            );

            if (!empty($result['order'])) {
                $this->order->update([
                    'api_order_id' => $result['order'],
                    'status'       => 'in progress',
                ]);
                Log::info("Order #{$this->order->id} successfully submitted to API. Provider Order ID: {$result['order']}");
            } else {
                throw new \Exception("Provider response missing order ID: " . json_encode($result));
            }
        } catch (\Throwable $e) {
            Log::error("API order submission failed for Order #{$this->order->id}: " . $e->getMessage());
            
            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("ProcessOrderJob permanently failed for Order #{$this->order->id}: " . $exception->getMessage());
        
        $this->order->update([
            'status' => 'error'
        ]);
    }
}
