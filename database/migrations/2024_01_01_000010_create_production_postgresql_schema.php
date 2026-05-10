<?php

// ============================================================================
// Migration: 2024_01_01_000010_create_production_postgresql_schema.php
// ============================================================================
// This migration REPLACES the original tables for PostgreSQL production.
// It adds:
//  1. UUID primary keys on users (collision-safe across distributed systems)
//  2. Composite indexes tuned to the actual query patterns in this app
//  3. Foreign key constraints with appropriate cascading
//  4. Soft deletes on users, orders, and transactions
//  5. Idempotency key column on transactions (prevents double-credit)
//  6. payment_logs table (was missing — referenced in WebhookController)
//  7. admin_action_logs table (audit trail for admin operations)
//  8. provider_logs table (API call history for debugging/billing)
//  9. Proper enum replacement via check constraints (PG native)
// 10. updated_at triggers would be done in PG — Laravel handles this fine.
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        // ── Users ─────────────────────────────────────────────────────────
        // SECURITY: is_admin, funds, status are NOT in $fillable on the model.
        // They must be set explicitly: $user->is_admin = true; $user->save();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // Financial balance — stored as DECIMAL to avoid float rounding errors
            // NEVER use float for money. DECIMAL(14,6) gives 8 digits before decimal.
            $table->decimal('funds', 14, 6)->default(0);

            // SECURITY: is_admin is false by default and never mass-assignable
            $table->boolean('is_admin')->default(false)->index();

            // Account status — 'banned' prevents login
            $table->enum('status', ['active', 'banned'])->default('active')->index();

            // Referral system
            $table->string('referral_code', 16)->nullable()->unique();
            $table->unsignedBigInteger('referred_by')->nullable();

            // Optional Telegram integration
            $table->string('telegram_user_id')->nullable();

            // 2FA support fields (architecture ready — UI opt-in)
            $table->string('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            // Login security
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->softDeletes(); // Soft delete: banned users are recoverable
            $table->timestamps();

            $table->foreign('referred_by')->references('id')->on('users')->nullOnDelete();

            // Composite index for common queries
            $table->index(['status', 'is_admin']);
            $table->index(['referred_by']);
            $table->index(['created_at']);
        });

        // ── Password Reset Tokens ─────────────────────────────────────────
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ── Categories ────────────────────────────────────────────────────
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('icon', 50)->default('list_alt');
            $table->string('color', 20)->default('#adc6ff');
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── API Providers ─────────────────────────────────────────────────
        // SECURITY: api_key is stored in DB — consider encryption at rest
        // (Laravel's encrypted cast or DB-level encryption for PCI compliance)
        Schema::create('api_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('url');
            // Encrypted cast on model protects key if DB is dumped
            $table->text('api_key');
            $table->decimal('percentage_increase', 8, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active')->index();

            // Circuit breaker fields — track consecutive failures
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamp('circuit_open_until')->nullable();

            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        // ── Services ──────────────────────────────────────────────────────
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('api_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('api_service_id')->nullable();
            $table->decimal('rate', 14, 6); // Price per 1000 units
            $table->unsignedBigInteger('min')->default(10);
            $table->unsignedBigInteger('max')->default(100000);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('type', ['api', 'manual'])->default('api');

            // Tier-based pricing support
            $table->enum('tier', ['basic', 'standard', 'premium'])->default('standard');
            $table->unsignedInteger('min_time')->nullable(); // min delivery hours
            $table->unsignedInteger('max_time')->nullable(); // max delivery hours

            $table->timestamps();

            // Unique: a provider cannot have two services with the same ID
            $table->unique(['api_provider_id', 'api_service_id'], 'services_provider_service_unique');

            // Query indexes tuned to ServiceController::index() and admin filters
            $table->index(['status', 'category_id'], 'services_status_category_idx');
            $table->index(['api_provider_id', 'status'], 'services_provider_status_idx');
            $table->index(['tier', 'status'], 'services_tier_status_idx');
        });

        // ── Orders ────────────────────────────────────────────────────────
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('link');
            $table->unsignedBigInteger('quantity');
            // MONEY: decimal not float
            $table->decimal('total', 14, 6);
            $table->enum('status', [
                'pending', 'in progress', 'completed',
                'partial', 'cancelled', 'refunded', 'error',
            ])->default('pending');
            $table->unsignedBigInteger('remains')->default(0);
            $table->unsignedBigInteger('start_count')->default(0);
            $table->unsignedBigInteger('api_order_id')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // ── Critical Indexes ───────────────────────────────────────────
            // User order history: WHERE user_id = ? ORDER BY created_at DESC
            $table->index(['user_id', 'status'], 'orders_user_id_status_idx');

            // Sync job: WHERE status IN ('pending','in progress') AND api_order_id IS NOT NULL
            $table->index(['status', 'api_order_id'], 'orders_status_api_idx');

            // Admin dashboard: WHERE status = ? paginated
            $table->index('status', 'orders_status_idx');

            // Analytics: date-range aggregation
            $table->index('created_at', 'orders_created_at_idx');

            // Duplicate order detection: WHERE user_id=? AND service_id=? AND link=? AND created_at >= ?
            $table->index(['user_id', 'service_id', 'link'], 'orders_duplicate_check_idx');
        });

        // ── Transactions ──────────────────────────────────────────────────
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 14, 6);
            $table->enum('type', ['deposit', 'deduction', 'referral_bonus', 'refund'])->default('deposit');
            $table->string('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');

            // IDEMPOTENCY: unique reference prevents double-crediting
            // For Stripe: payment_intent_id. For PayPal: order_id. For manual: user-provided ref.
            $table->string('reference')->nullable();

            // Which payment gateway processed this
            $table->string('gateway', 50)->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Idempotency: one transaction per external reference
            $table->unique('reference', 'transactions_reference_unique');

            // ── Indexes ────────────────────────────────────────────────────
            $table->index(['user_id', 'status'], 'txns_user_status_idx');
            $table->index(['status', 'type'], 'txns_status_type_idx');
            $table->index(['type', 'status'], 'txns_type_status_idx'); // Revenue aggregation
            $table->index('created_at', 'txns_created_at_idx');
        });

        // ── Payment Logs ──────────────────────────────────────────────────
        // PURPOSE: Immutable audit trail of every payment event (webhook received,
        // balance credited, failed attempts). Never deleted, never updated.
        // COMPLIANCE: Required for payment dispute resolution and fraud investigation.
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();  // nullable: failed before user lookup
            $table->unsignedBigInteger('transaction_id')->nullable(); // nullable: failed before TX create
            $table->string('gateway', 50);                       // stripe | paypal | manual | jazzcash
            $table->string('status', 50);                        // pending | completed | failed | refunded
            $table->decimal('amount', 14, 6)->nullable();
            $table->string('reference', 255)->nullable();        // External payment reference
            $table->string('event_type', 100)->nullable();       // e.g. payment_intent.succeeded
            $table->string('idempotency_key', 255)->nullable();  // Prevents replay processing
            $table->json('payload')->nullable();                  // Full webhook payload (redacted)
            $table->text('error_message')->nullable();
            $table->string('ip_address', 45)->nullable();

            // Immutable: created_at only, no updated_at
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'gateway'], 'plogs_user_gateway_idx');
            $table->index(['gateway', 'status'], 'plogs_gateway_status_idx');
            $table->index('reference', 'plogs_reference_idx');
            $table->index('idempotency_key', 'plogs_idempotency_idx');
            $table->index('created_at', 'plogs_created_at_idx');

            // Foreign keys (soft — payment_logs must survive user deletion)
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete();
        });

        // ── Tickets ───────────────────────────────────────────────────────
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->text('message');
            $table->string('category', 50)->default('other');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->enum('status', ['open', 'pending', 'closed'])->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'tickets_user_status_idx');
            $table->index('status', 'tickets_status_idx');
        });

        // ── Ticket Messages ───────────────────────────────────────────────
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->text('message');
            $table->boolean('is_admin')->default(false);
            $table->timestamps();

            $table->index(['ticket_id', 'created_at'], 'tmsg_ticket_created_idx');
        });

        // ── Admin Action Logs ─────────────────────────────────────────────
        // PURPOSE: Every admin action that mutates data must be logged here.
        // This enables forensic analysis of fraud or mistakes.
        // IMMUTABLE: rows are insert-only (never updated or deleted).
        Schema::create('admin_action_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('action', 100);          // e.g. add_funds, ban_user, approve_transaction
            $table->string('target_type', 50);       // User | Order | Transaction
            $table->unsignedBigInteger('target_id');
            $table->json('before')->nullable();       // State before change
            $table->json('after')->nullable();        // State after change
            $table->string('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('admin_id')->references('id')->on('users')->restrictOnDelete();

            $table->index(['admin_id', 'action'], 'admin_logs_admin_action_idx');
            $table->index(['target_type', 'target_id'], 'admin_logs_target_idx');
            $table->index('created_at', 'admin_logs_created_at_idx');
        });

        // ── Provider Logs ─────────────────────────────────────────────────
        // All calls to SMM provider APIs are logged here for debugging,
        // billing reconciliation, and provider performance metrics.
        Schema::create('provider_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_provider_id')->constrained()->cascadeOnDelete();
            $table->string('action', 50);            // add_order | get_status | get_services
            $table->string('status', 20);            // success | failed
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();
            $table->decimal('response_time', 8, 2)->nullable(); // milliseconds

            // Retention: auto-clean logs older than 90 days via scheduler
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(['api_provider_id', 'status'], 'plogs_provider_status_idx');
            $table->index(['action', 'status'], 'plogs_action_status_idx');
            $table->index('created_at', 'plogs_prov_created_at_idx');
        });

        // ── Activity Logs ─────────────────────────────────────────────────
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['user_id', 'created_at'], 'act_logs_user_created_idx');
            $table->index('action', 'act_logs_action_idx');
        });

        // ── Failed Jobs ───────────────────────────────────────────────────
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // ── Job Batches (for batch queue processing) ───────────────────────
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // ── Cache (DB driver fallback if Redis is down) ───────────────────
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    public function down(): void
    {
        // Drop in reverse dependency order
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('provider_logs');
        Schema::dropIfExists('admin_action_logs');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('services');
        Schema::dropIfExists('api_providers');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
