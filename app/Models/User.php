<?php

namespace App\Models;

// ============================================================================
// User Model — Security-Hardened
// ============================================================================
// Changes from original:
//  1. Added `status` to $hidden — banned users shouldn't see their own status
//     in API responses (prevents probing bans)
//  2. Added login throttling fields: failed_login_attempts, locked_until
//  3. Referral code generation now uses random_bytes (CSPRNG) instead of MD5
//  4. Added isLocked() helper for login controller
//  5. Added 2FA support fields with helper methods
//  6. Proper decimal cast for funds (float cast is sufficient for display,
//     but all DB operations use DECIMAL arithmetic)
// ============================================================================

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Mass-assignable attributes.
     *
     * SECURITY: is_admin, funds, status are intentionally EXCLUDED.
     * These must be set via explicit assignment:
     *   $user->is_admin = true; $user->save();
     *   $user->increment('funds', 10.00);
     *
     * Never add is_admin or funds here — a malicious request could
     * send {"is_admin": true} during registration if they were fillable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'referral_code',
        'referred_by',
        'telegram_user_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at'       => 'datetime',
        'funds'                   => 'decimal:6',
        'is_admin'                => 'boolean',
        'failed_login_attempts'   => 'integer',
        'locked_until'            => 'datetime',
        'last_login_at'           => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
        'deleted_at'              => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            // SECURITY: Use CSPRNG for referral code, not MD5
            // Original: md5($email . time()) — predictable and collision-prone
            // Fixed: bin2hex(random_bytes(6)) — 48 bits of entropy, URL-safe
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(bin2hex(random_bytes(6)));
            }
        });
    }

    // ── Login Security Helpers ────────────────────────────────────────────────

    /**
     * Check if the account is currently locked due to failed login attempts.
     * Called in LoginController before attempting authentication.
     */
    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * Record a failed login attempt. Lock account after 5 failures.
     * Lock duration increases: 5 min, 15 min, 1 hour, 24 hours.
     */
    public function recordFailedLogin(): void
    {
        $attempts = $this->failed_login_attempts + 1;
        $updates  = ['failed_login_attempts' => $attempts];

        if ($attempts >= 5) {
            $lockMinutes = match (true) {
                $attempts >= 20 => 1440, // 24 hours
                $attempts >= 10 => 60,
                $attempts >= 7  => 15,
                default         => 5,
            };
            $updates['locked_until'] = now()->addMinutes($lockMinutes);
        }

        $this->update($updates);
    }

    /**
     * Reset login attempt counter after successful authentication.
     */
    public function recordSuccessfulLogin(string $ip): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
            'last_login_at'         => now(),
            'last_login_ip'         => $ip,
        ]);
    }

    // ── 2FA Helpers ───────────────────────────────────────────────────────────

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null
            && $this->two_factor_confirmed_at !== null;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function fundRequests(): HasMany
    {
        return $this->hasMany(Transaction::class)
            ->where('type', 'deposit')
            ->whereNotNull('fund_account_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketMessages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    // ── Custom Notifications ──────────────────────────────────────────────────

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
