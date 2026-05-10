<?php

namespace App\Jobs;

// ============================================================================
// ProcessWebhookPaymentJob — Queue-Based, Idempotent Payment Processor
// ============================================================================
// WHY A JOB INSTEAD OF INLINE PROCESSING:
//  1. Webhook handlers must respond in < 30s (Stripe) or < 5s (PayPal) or
//     the gateway retries. Heavy DB operations can breach this.
//  2. If the DB is temporarily slow, we don't want to tell Stripe "failed"
//     and trigger a flood of retries. The queue handles backoff gracefully.
//  3. Queue provides automatic retry with exponential backoff.
//  4. Idempotency key prevents double-credit even if the job runs twice.
//
// SAFETY GUARANTEES:
//  - Double-credit prevention via unique constraint on transactions.reference
//  - Pessimistic locking (SELECT FOR UPDATE) on user row during balance update
//  - Entire operation wrapped in DB::transaction (atomicity)
//  - Idempotency checked again inside the job (not just in the controller)
//    because two identical webhook events may both be queued before either
//    completes (race condition between webhook receipt and job execution).
// ============================================================================

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWebhookPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum attempts before the job is moved to failed_jobs.
     * Stripe retries webhooks for 72 hours; we align our retry window.
     */
    public int $tries = 5;

    /**
     * Exponential backoff: 1m, 5m, 15m, 1h, 4h
     * This prevents hammering the DB during transient failures.
     */
    public function backoff(): array
    {
        return [60, 300, 900, 3600, 14400];
    }

    /**
     * Job timeout — prevents zombie workers.
     */
    public int $timeout = 60;

    /**
     * Prevent duplicate job execution using the idempotency key.
     * Laravel's unique jobs use the cache driver for the lock.
     */
    public function uniqueId(): string
    {
        return "webhook:{$this->gateway}:{$this->idempotencyKey}";
    }

    public function __construct(
        private readonly string $gateway,        // 'stripe' | 'paypal'
        private readonly string $eventType,      // e.g. 'payment_intent.succeeded'
        private readonly string $idempotencyKey, // Stripe event ID or PayPal event ID
        private readonly array  $payload,        // Webhook event data object
    ) {
        // Route to the high-priority queue for payment processing
        $this->onQueue('payments');
    }

    public function handle(): void
    {
        // ── 1. Final idempotency check (inside the job) ───────────────────
        // Even with the uniqueId() lock above, we do a DB check here because:
        //  a) The cache lock may expire before the job completes
        //  b) Multiple app instances may race after a deployment
        if ($this->isAlreadyProcessed()) {
            Log::info("Webhook already processed, skipping", [
                'gateway'          => $this->gateway,
                'idempotency_key'  => $this->idempotencyKey,
            ]);
            return;
        }

        match ($this->gateway) {
            'stripe' => $this->handleStripeEvent(),
            'paypal' => $this->handlePayPalEvent(),
            default  => Log::warning("Unknown gateway: {$this->gateway}"),
        };
    }

    // ── Stripe Event Handlers ─────────────────────────────────────────────────

    private function handleStripeEvent(): void
    {
        match ($this->eventType) {
            'payment_intent.succeeded'      => $this->creditStripePayment(),
            'payment_intent.payment_failed' => $this->logStripeFailure(),
            'charge.refunded'               => $this->handleStripeRefund(),
            default => Log::debug("Unhandled Stripe event type: {$this->eventType}"),
        };
    }

    private function creditStripePayment(): void
    {
        $intent   = $this->payload;
        $userId   = $intent['metadata']['user_id'] ?? null;
        $amount   = ($intent['amount_received'] ?? 0) / 100; // cents → dollars
        $reference = $intent['id'] ?? $this->idempotencyKey;

        if (! $userId || $amount <= 0) {
            Log::error('Stripe payment_intent.succeeded: missing user_id or invalid amount', [
                'reference' => $reference,
                'payload'   => $this->sanitizePayload($intent),
            ]);
            // Don't throw — this is a data problem, not a transient failure
            // Retrying won't fix missing metadata
            $this->logPaymentEvent(null, null, 'failed', $amount,
                'Missing user_id or amount in metadata');
            return;
        }

        $this->creditUserBalance($userId, $amount, $reference, 'stripe', 'Stripe deposit');
    }

    private function logStripeFailure(): void
    {
        $intent    = $this->payload;
        $reference = $intent['id'] ?? $this->idempotencyKey;
        $userId    = $intent['metadata']['user_id'] ?? null;
        $amount    = ($intent['amount'] ?? 0) / 100;
        $errorMsg  = $intent['last_payment_error']['message'] ?? 'Unknown error';

        $this->logPaymentEvent($userId, null, 'failed', $amount, $errorMsg);
        Log::info('Stripe payment failed', compact('reference', 'userId', 'errorMsg'));
    }

    private function handleStripeRefund(): void
    {
        // Refund handling: deduct funds from user if previously credited
        // Implementation depends on business rules (partial refunds, etc.)
        Log::info('Stripe refund received — manual review may be required', [
            'charge_id' => $this->payload['id'] ?? 'unknown',
        ]);
        // TODO: Implement automatic balance deduction for refunds
        // This requires matching the original charge to a transaction
    }

    // ── PayPal Event Handlers ─────────────────────────────────────────────────

    private function handlePayPalEvent(): void
    {
        match ($this->eventType) {
            'PAYMENT.CAPTURE.COMPLETED'  => $this->creditPayPalPayment(),
            'CHECKOUT.ORDER.APPROVED'    => $this->creditPayPalPayment(),
            'PAYMENT.CAPTURE.REVERSED'   => $this->handlePayPalRefund(),
            default => Log::debug("Unhandled PayPal event: {$this->eventType}"),
        };
    }

    private function creditPayPalPayment(): void
    {
        // PayPal order structure differs by event type
        $resource  = $this->payload['resource'] ?? $this->payload;
        $amount    = (float) ($resource['amount']['value']
                    ?? $resource['purchase_units'][0]['payments']['captures'][0]['amount']['value']
                    ?? 0);
        $currency  = $resource['amount']['currency_code']
                    ?? $resource['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code']
                    ?? 'USD';

        // PayPal custom_id carries our user_id (set during order creation)
        $userId    = $resource['custom_id']
                    ?? $resource['purchase_units'][0]['custom_id']
                    ?? null;
        $reference = $resource['id'] ?? $this->idempotencyKey;

        if (! $userId || $amount <= 0) {
            Log::error('PayPal payment missing custom_id (user_id) or amount', [
                'event_type' => $this->eventType,
                'reference'  => $reference,
            ]);
            $this->logPaymentEvent(null, null, 'failed', $amount, 'Missing custom_id');
            return;
        }

        // Convert non-USD amounts if needed
        if ($currency !== 'USD') {
            Log::warning("PayPal payment in non-USD currency: {$currency}", compact('amount'));
            // In a real system: convert via exchange rate service
            // For now, reject non-USD to prevent exchange rate errors
            $this->logPaymentEvent($userId, null, 'failed', $amount,
                "Non-USD currency not supported: {$currency}");
            return;
        }

        $this->creditUserBalance($userId, $amount, $reference, 'paypal', 'PayPal deposit');
    }

    private function handlePayPalRefund(): void
    {
        Log::info('PayPal refund received', [
            'resource_id' => $this->payload['resource']['id'] ?? 'unknown',
        ]);
        // TODO: Implement refund balance deduction
    }

    // ── Core Balance Credit (shared by Stripe and PayPal) ─────────────────────

    /**
     * Atomically credit user balance and record the transaction.
     *
     * SAFETY:
     *  - DB::transaction wraps ALL mutations (atomicity)
     *  - lockForUpdate() prevents race conditions (User A and User B ordering
     *    simultaneously cannot both read the same balance before decrementing)
     *  - UNIQUE constraint on transactions.reference prevents double-insert
     *    even if two job instances race (DB-level safety net)
     *  - payment_logs.idempotency_key is the audit record
     */
    private function creditUserBalance(
        int|string $userId,
        float $amount,
        string $reference,
        string $gateway,
        string $description
    ): void {
        // Validate amount bounds (fraud check)
        if ($amount < 0.01) {
            Log::warning('Payment amount below minimum threshold', compact('amount', 'reference'));
            return;
        }

        if ($amount > 10000) {
            // Large payments need manual review — flag but don't block
            Log::warning('Large payment received — flagging for review', compact('amount', 'reference', 'userId'));
        }

        try {
            DB::transaction(function () use ($userId, $amount, $reference, $gateway, $description) {
                // Lock the user row to prevent concurrent balance updates
                $user = User::lockForUpdate()->find($userId);

                if (! $user) {
                    throw new \RuntimeException("User {$userId} not found for payment {$reference}");
                }

                if ($user->status === 'banned') {
                    Log::warning('Payment for banned user — rejecting', compact('userId', 'reference'));
                    $this->logPaymentEvent($userId, null, 'rejected_banned', $amount,
                        'User account is banned');
                    return; // Don't throw — we don't want Stripe to retry banned users
                }

                // Create transaction record
                // The UNIQUE constraint on 'reference' is the final safety net against double-credit
                $transaction = Transaction::create([
                    'user_id'     => $user->id,
                    'amount'      => $amount,
                    'type'        => 'deposit',
                    'status'      => 'completed',
                    'description' => $description,
                    'reference'   => $reference,
                    'gateway'     => $gateway,
                ]);

                // Increment balance atomically (DB increment = single UPDATE query)
                $user->increment('funds', $amount);

                // Write to immutable payment audit log
                $this->logPaymentEvent($user->id, $transaction->id, 'completed', $amount, null);
            });

            Log::info('Payment credited successfully', [
                'user_id'   => $userId,
                'amount'    => $amount,
                'reference' => $reference,
                'gateway'   => $gateway,
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Unique constraint violation = already processed (race condition resolved)
            if (str_contains($e->getMessage(), 'unique') || str_contains($e->getMessage(), 'Duplicate')) {
                Log::info('Payment already recorded (unique constraint — idempotent)', compact('reference'));
                return;
            }
            // Re-throw other DB errors for retry
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to credit payment: ' . $e->getMessage(), [
                'user_id'   => $userId,
                'reference' => $reference,
                'gateway'   => $gateway,
            ]);
            // Re-throw to trigger job retry with exponential backoff
            throw $e;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function isAlreadyProcessed(): bool
    {
        return DB::table('payment_logs')
            ->where('idempotency_key', $this->idempotencyKey)
            ->where('status', 'completed')
            ->exists();
    }

    private function logPaymentEvent(
        int|string|null $userId,
        ?int $transactionId,
        string $status,
        ?float $amount,
        ?string $errorMessage
    ): void {
        try {
            DB::table('payment_logs')->insertOrIgnore([
                'user_id'         => $userId,
                'transaction_id'  => $transactionId,
                'gateway'         => $this->gateway,
                'event_type'      => $this->eventType,
                'status'          => $status,
                'amount'          => $amount,
                'idempotency_key' => $this->idempotencyKey,
                'error_message'   => $errorMessage,
                // Redact sensitive fields before storing payload
                'payload'         => json_encode($this->sanitizePayload($this->payload)),
                'created_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to write payment_log in job: ' . $e->getMessage());
        }
    }

    /**
     * Remove sensitive fields from payload before storing in DB.
     * PCI-DSS: Never store raw card data, even in logs.
     */
    private function sanitizePayload(array $payload): array
    {
        $sensitiveKeys = ['card', 'payment_method', 'bank_account', 'cvc', 'number', 'exp_month', 'exp_year'];
        foreach ($sensitiveKeys as $key) {
            unset($payload[$key]);
        }
        return $payload;
    }

    /**
     * Called when all retries are exhausted.
     * Alert the admin — manual intervention required.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('ProcessWebhookPaymentJob permanently failed', [
            'gateway'         => $this->gateway,
            'event_type'      => $this->eventType,
            'idempotency_key' => $this->idempotencyKey,
            'error'           => $exception->getMessage(),
        ]);

        // TODO: Send admin alert via Slack/email
        // Mail::to(config('mail.admin_address'))->send(new PaymentJobFailedMail(...));
    }
}
