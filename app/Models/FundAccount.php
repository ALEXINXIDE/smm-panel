<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundAccount extends Model
{
    protected $fillable = [
        'name',
        'iban',
        'account_number',
        'notes',
        'status',
        'is_active',  // ← new canonical toggle
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────────

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ── Query Scopes ──────────────────────────────────────────────────────

    /**
     * Only accounts explicitly enabled by an admin.
     * Uses `is_active` — the single source of truth going forward.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /** Toggle active state and persist. */
    public function toggle(): void
    {
        $this->is_active = !$this->is_active;
        // Keep legacy status column in sync
        $this->status = $this->is_active ? 'active' : 'inactive';
        $this->save();
    }
}
