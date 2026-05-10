<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ExchangeRateService;

class InjectExchangeRate
{
    public function handle(Request $request, Closure $next)
    {
        // FIXED MEDIUM-4: Use shared cache only — not per-session
        // All users get the same rate from one cache entry
        $rate = ExchangeRateService::getUsdToPkr();

        // Share with all views globally
        view()->share('usd_pkr_rate', $rate);

        // Also keep in session for JS access in Blade templates
        if (! session()->has('usd_pkr_rate') ||
            abs(session('usd_pkr_rate', 0) - $rate) > 1) {
            session(['usd_pkr_rate' => $rate]);
        }

        return $next($request);
    }
}
