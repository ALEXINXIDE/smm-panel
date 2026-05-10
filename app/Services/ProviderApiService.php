<?php

namespace App\Services;

use App\Models\ApiProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ProviderApiService
 *
 * FIXES:
 * - CRITICAL-2: SSL verification re-enabled (verify: true)
 * - Added provider_logs DB logging for every API call
 * - Added response-time measurement
 * - Added typed return types
 */
class ProviderApiService
{
    protected ApiProvider $provider;
    protected Client $client;

    public function __construct(ApiProvider $provider)
    {
        $this->provider = $provider;
        $this->client   = new Client([
            'timeout'     => 15,
            'verify'      => true,   // ← FIXED: never disable TLS verification
            'http_errors' => false,
        ]);
    }

    public function getServices(): array
    {
        return $this->call('get_services', ['action' => 'services']);
    }

    public function getBalance(): float
    {
        $response = $this->call('get_balance', ['action' => 'balance']);
        return (float) ($response['balance'] ?? 0);
    }

    public function addOrder(int $serviceId, string $link, int $qty): array
    {
        return $this->call('add_order', [
            'action'   => 'add',
            'service'  => $serviceId,
            'link'     => $link,
            'quantity' => $qty,
        ]);
    }

    public function getStatus(int $orderId): array
    {
        return $this->call('get_status', [
            'action' => 'status',
            'order'  => $orderId,
        ]);
    }

    /**
     * Bulk status check — pass comma-separated IDs as the provider expects.
     */
    public function getStatusBulk(array $ids): array
    {
        return $this->call('get_status_bulk', [
            'action' => 'status',
            'orders' => implode(',', $ids),
        ]);
    }

    public function requestRefill(int $orderId): array
    {
        return $this->call('refill', [
            'action' => 'refill',
            'order'  => $orderId,
        ]);
    }

    /**
     * Central HTTP call with logging and error handling.
     */
    private function call(string $action, array $params): array
    {
        $start  = microtime(true);
        $status = 'failed';
        $body   = [];
        $error  = null;

        try {
            $response = $this->client->post($this->provider->url, [
                'form_params' => array_merge(
                    ['key' => $this->provider->api_key],
                    $params
                ),
            ]);

            $statusCode = $response->getStatusCode();
            $raw        = (string) $response->getBody();
            $body       = json_decode($raw, true) ?? [];

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON response from provider: ' . substr($raw, 0, 200));
            }

            if (! empty($body['error'])) {
                throw new \RuntimeException('Provider error: ' . $body['error']);
            }

            if ($statusCode !== 200) {
                Log::warning("Provider [{$this->provider->name}] HTTP {$statusCode}", compact('params'));
            }

            $status = 'success';
            return $body;

        } catch (\Throwable $e) {
            $error = $e->getMessage();

            Log::error("ProviderApiService [{$this->provider->name}] {$action}: {$error}", [
                'provider_id' => $this->provider->id,
            ]);

            throw $e;

        } finally {
            $elapsed = round((microtime(true) - $start) * 1000, 2); // ms

            // Log every call to provider_logs table for admin visibility
            try {
                DB::table('provider_logs')->insert([
                    'api_provider_id' => $this->provider->id,
                    'action'          => $action,
                    'status'          => $status,
                    'request'         => json_encode($params),
                    'response'        => json_encode($body),
                    'error_message'   => $error,
                    'response_time'   => $elapsed,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            } catch (\Throwable $logEx) {
                // Never crash the main flow due to logging failure
                Log::warning('Failed to write provider_log: ' . $logEx->getMessage());
            }
        }
    }
}
