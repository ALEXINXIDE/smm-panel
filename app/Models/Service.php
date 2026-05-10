<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    // ── Mass-assignable ───────────────────────────────────────────────────
    protected $fillable = [
        'name', 'description', 'category_id', 'api_provider_id',
        'api_service_id', 'rate', 'min', 'max', 'status', 'type',
        'tier', 'min_time', 'max_time',
    ];

    protected $casts = [
        'rate'     => 'float',
        'min'      => 'integer',
        'max'      => 'integer',
        'min_time' => 'integer',
        'max_time' => 'integer',
    ];

    // ── Relations ─────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function apiProvider(): BelongsTo
    {
        return $this->belongsTo(ApiProvider::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ── Query Scopes ──────────────────────────────────────────────────────

    /** Only published/active services. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Filter by platform via the category relation.
     * Uses a JOIN so a single query handles both filter and sort.
     */
    public function scopeForPlatform(Builder $query, ?string $platform): Builder
    {
        if (blank($platform)) {
            return $query;
        }

        return $query->whereHas('category', function (Builder $q) use ($platform) {
            $q->where('platform', $platform);
        });
    }

    /**
     * Filter by service type (followers, likes, views…) via category.
     */
    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        if (blank($type)) {
            return $query;
        }

        return $query->whereHas('category', function (Builder $q) use ($type) {
            $q->where('type', $type);
        });
    }

    /**
     * Full-text-style name search (ILIKE / LIKE depending on driver).
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where('name', 'like', '%' . $term . '%');
    }

    /**
     * Sort by cheapest first (default) or another allowed column.
     *
     * @param  'price'|'name'  $sort
     */
    public function scopeSorted(Builder $query, string $sort = 'price'): Builder
    {
        return match ($sort) {
            'name'  => $query->orderBy('name'),
            default => $query->orderBy('rate'), // cheapest first
        };
    }
}
