<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

// Stripe + PayPal webhooks — CSRF excluded in VerifyCsrfToken
Route::post('webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('webhooks/paypal', [WebhookController::class, 'paypal'])->name('webhooks.paypal');

// Health check — monitored by external status page
Route::get('health', [\App\Http\Controllers\HealthController::class, 'check'])->name('api.health');
