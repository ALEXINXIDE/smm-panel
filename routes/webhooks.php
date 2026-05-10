<?php

// ============================================================================
// routes/web.php — Production Route Definitions (additions/changes only)
// ============================================================================
// Changes from original:
//  1. Webhook routes added (they were missing from original routes file)
//  2. Health check routes added
//  3. Diagnostics endpoint added (admin only)
//  4. Webhook routes have no 'auth' middleware (server-to-server)
//  5. Webhook routes have throttle middleware for DDoS protection
//
// NOTE: Only changes are shown here. Merge with existing routes/web.php.
// The full original routes remain unchanged.
// ============================================================================

use App\Http\Controllers\HealthController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// ── Health & Monitoring ───────────────────────────────────────────────────────

// Liveness probe — used by Docker healthcheck, Kubernetes, load balancers
// Returns 200 if PHP is running. No DB check.
Route::get('/up', [HealthController::class, 'up'])->name('health.up');

// Readiness probe — checks DB + Redis. Returns 503 if dependencies are down.
// Load balancer will stop routing to this pod if /health returns 503.
Route::get('/health', [HealthController::class, 'check'])->name('health.check');

// Full diagnostics — admin auth required
Route::middleware(['auth', 'admin'])
    ->get('/diagnostics', [HealthController::class, 'diagnostics'])
    ->name('health.diagnostics');


// ── Payment Webhooks ──────────────────────────────────────────────────────────
// IMPORTANT: These routes are excluded from CSRF in VerifyCsrfToken::$except.
// They have their own cryptographic verification (Stripe sig / PayPal cert).
//
// Throttle: 60 requests per minute per IP.
// Stripe may send burst retries; 60/min allows that without accepting DDoS.

Route::prefix('webhooks')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::post('stripe', [WebhookController::class, 'stripe'])
            ->name('webhooks.stripe');

        Route::post('paypal', [WebhookController::class, 'paypal'])
            ->name('webhooks.paypal');
    });
