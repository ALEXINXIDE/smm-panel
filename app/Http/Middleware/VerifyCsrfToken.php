<?php

// ============================================================================
// VerifyCsrfToken.php — CSRF Exclusion for Webhooks
// ============================================================================
// Payment provider webhooks are server-to-server requests that cannot
// carry CSRF tokens. They must be excluded from CSRF verification.
//
// SECURITY: This does NOT weaken security for webhooks because:
//  - Stripe webhooks are verified via HMAC-SHA256 signature
//  - PayPal webhooks are verified via PayPal's verification API
//  - These cryptographic verifications are stronger than CSRF tokens
//
// NEVER exclude user-facing routes from CSRF — only server-to-server webhooks.
// ============================================================================

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs excluded from CSRF verification.
     * These are server-to-server webhook endpoints only.
     */
    protected $except = [
        'webhooks/stripe',
        'webhooks/paypal',
    ];
}
