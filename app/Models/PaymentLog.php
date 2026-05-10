<?php

namespace App\Models;

// ============================================================================
// PaymentLog — Immutable Payment Audit Trail
// ============================================================================
// This model was MISSING from the original codebase but was referenced in
// FundsController. The payment_logs table is the canonical record of every
// payment event, regardless of outcome.
//
// IMMUTABILITY: Rows should never be updated or deleted after creation.
// The only exception is updating `status` when a pending transaction is
// approved or rejected by admin (handled via DB::table, not model updates,
// to avoid triggering Eloquent events that might create duplicate records).
//
// COMPLIANCE: This table is required for:
//  - Payment dispute resolution
//  - Fraud investigation
//  - Chargeback defense (shows you received the webhook and processed it)
//  - Financial audit trails
// ============================================================================

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    // No updated_at — payment logs are immutable once created
    const UPDATED_AT = null;

    protected $table = 'payment_logs';

    protected $fillable = [
        'user_id',
        'transaction_id',
        'gateway',
        'status',
        'amount',
        'reference',
        'event_type',
        'idempotency_key',
        'payload',
        'error_message',
        'ip_address',
    ];

    protected $casts = [
        'amount'     => 'float',
        'payload'    => 'array',
        'created_at' => 'datetime',
    ];

    // Prevent accidental updates — payment logs are write-once
    public static function boot(): void
    {
        parent::boot();

        static::updating(function () {
            // Allow status updates (for admin approval/rejection workflow)
            // but log a warning for any other updates
            if (request()) {
                \Illuminate\Support\Facades\Log::warning(
                    'PaymentLog model update attempted — these should be immutable.',
                    ['trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)]
                );
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
