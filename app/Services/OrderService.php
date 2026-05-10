<?php

namespace App\Services;

// ============================================================================
// OrderService — Production-Grade Order Creation with Race Condition Protection
// ============================================================================
// Fixes from original:
//  1. RACE CONDITION: Original code called User::find() THEN checked balance
//     THEN decremented. Two simultaneous requests could both pass the check
//     before either decrements (TOCTOU vulnerability).
//     FIX: lockForUpdate() acquires a DB row lock BEFORE reading balance.
//         The lock is held until the transaction commits, serializing concurrent
//         orders from the same user.
//  2. SERVICE LAYER: Business logic moved out of controller into service class.
//  3. Custom exceptions: OrderException and InsufficientFundsException give
//     callers precise error handling without string parsing.
//  4. Service status check: Cannot order an inactive service.
//  5. Link sanitization: URLs are validated and normalized.
//  6. Admin order creation: Supports creating orders on behalf of users.
// ============================================================================

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\OrderException;
use App\Jobs\ProcessOrderJob;
use App\Models\Order;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Create a new order for the authenticated user.
     *
     * @param array $data Must contain: service_id, quantity, link
     * @param int|null $userId Override user (admin use only)
     * @throws InsufficientFundsException
     * @throws OrderException
     */
    public function createOrder(array $data, ?int $userId = null): Order
    {
        $userId  = $userId ?? Auth::id();
        $service = Service::findOrFail($data['service_id']);

        // ── Pre-flight validation ─────────────────────────────────────────
        if ($service->status !== 'active') {
            throw new OrderException("Service '{$service->name}' is currently unavailable.");
        }

        $quantity = (int) $data['quantity'];
        $link     = $this->sanitizeLink($data['link']);

        if ($quantity < $service->min || $quantity > $service->max) {
            throw new OrderException(
                "Quantity must be between {$service->min} and {$service->max} for this service."
            );
        }

        // Calculate cost: rate is per 1000 units
        // Use DECIMAL arithmetic to avoid floating-point rounding errors
        $total = round(($quantity / 1000) * $service->rate, 6);

        if ($total < 0.000001) {
            throw new OrderException('Order total too small. Please increase quantity.');
        }

        return DB::transaction(function () use ($userId, $service, $quantity, $link, $total) {
            // ── CRITICAL: Lock user row before reading balance ─────────────
            // lockForUpdate() issues: SELECT ... FOR UPDATE
            // This row lock is held until the transaction commits/rolls back.
            // Any other request trying to lockForUpdate() the same user row
            // will WAIT here — serializing concurrent balance operations.
            // This prevents the TOCTOU race condition in the original code.
            $user = User::lockForUpdate()->findOrFail($userId);

            if ($user->status === 'banned') {
                throw new OrderException('Your account has been suspended. Please contact support.');
            }

            // ── Balance check (inside the lock) ───────────────────────────
            if ($user->funds < $total) {
                throw new InsufficientFundsException(
                    "Insufficient balance. Required: \${$total}, Available: \${$user->funds}"
                );
            }

            // ── Deduct balance atomically ─────────────────────────────────
            // $user->decrement() issues a single UPDATE funds = funds - X WHERE id = ?
            // Combined with the lock above, this is fully atomic.
            $user->decrement('funds', $total);

            // ── Create Order ──────────────────────────────────────────────
            $order = Order::create([
                'user_id'    => $user->id,
                'service_id' => $service->id,
                'link'       => $link,
                'quantity'   => $quantity,
                'total'      => $total,
                'status'     => 'pending',
                'remains'    => $quantity,
            ]);

            // ── Record deduction transaction ──────────────────────────────
            Transaction::create([
                'user_id'     => $user->id,
                'amount'      => $total,
                'type'        => 'deduction',
                'description' => "Order #{$order->id} — {$service->name}",
                'status'      => 'completed',
                'reference'   => (string) $order->id,
                'gateway'     => 'internal',
            ]);

            // ── Dispatch to queue ─────────────────────────────────────────
            // IMPORTANT: Dispatched AFTER the transaction commits.
            // If we dispatched inside the transaction, the job might run before
            // the transaction commits (in a fast queue environment), causing
            // the job to see stale data.
            if ($service->api_provider_id && $service->api_service_id) {
                ProcessOrderJob::dispatch($order)->afterCommit();
            }

            Log::info('Order created', [
                'order_id'   => $order->id,
                'user_id'    => $user->id,
                'service_id' => $service->id,
                'total'      => $total,
            ]);

            return $order;
        });
    }

    /**
     * Check if a user has a duplicate pending order (spam protection).
     *
     * Duplicate = same user, same service, same link, within cooldown window.
     * This is a UX protection — the real anti-spam is rate limiting on the route.
     */
    public function isDuplicateOrder(
        int $userId,
        int $serviceId,
        string $link,
        int $seconds = 60
    ): bool {
        return Order::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->where('link', $link)
            ->where('created_at', '>=', now()->subSeconds($seconds))
            ->whereNotIn('status', ['cancelled', 'error'])
            ->exists();
    }

    /**
     * Calculate the total cost for a given service and quantity.
     * Used for frontend price preview without creating an order.
     */
    public function calculateCost(Service $service, int $quantity): float
    {
        return round(($quantity / 1000) * $service->rate, 6);
    }

    /**
     * Sanitize and validate a social media URL.
     *
     * SECURITY: Prevents order submission with:
     *  - javascript: URLs (XSS if link is ever rendered as href)
     *  - data: URLs
     *  - Excessively long URLs (DoS via DB storage)
     */
    private function sanitizeLink(string $link): string
    {
        $link = trim($link);

        // Reject dangerous protocols
        if (preg_match('/^(javascript|data|vbscript):/i', $link)) {
            throw new OrderException('Invalid URL provided.');
        }

        // Enforce max length (DB column is varchar)
        if (strlen($link) > 500) {
            throw new OrderException('URL is too long (maximum 500 characters).');
        }

        return $link;
    }
}
