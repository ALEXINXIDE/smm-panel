<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    public static function getUsdToPkr(): float
    {
        return Cache::remember('usd_pkr_rate', 86400, function () {
            $sources = [
                'https://open.er-api.com/v6/latest/USD',
                'https://api.exchangerate-api.com/v4/latest/USD',
            ];
            foreach ($sources as $url) {
                try {
                    $r = Http::timeout(5)->get($url);
                    if ($r->successful() && isset($r->json()['rates']['PKR'])) {
                        $rate = (float) $r->json()['rates']['PKR'];
                        Log::info('PKR rate refreshed', ['rate' => $rate, 'source' => $url]);
                        return $rate;
                    }
                } catch (\Throwable $e) {
                    Log::warning("ExchangeRate {$url} failed: " . $e->getMessage());
                }
            }
            return 280.0; // last-resort fallback
        });
    }

    public static function refresh(): float
    {
        Cache::forget('usd_pkr_rate');
        return self::getUsdToPkr();
    }
}
