<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    // ── Allowed platforms & types ─────────────────────────────────────────
    public const PLATFORMS = [
        'instagram', 'tiktok', 'youtube', 'facebook', 'twitter',
    ];

    public const TYPES = [
        'followers', 'likes', 'views', 'comments', 'shares',
    ];

    // ── Mass-assignable ───────────────────────────────────────────────────
    protected $fillable = [
        'name', 'icon', 'color', 'status',
        'platform', 'type', 'sort_order',
    ];

    // ── Relations ─────────────────────────────────────────────────────────

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    // ── Query Scopes ──────────────────────────────────────────────────────

    /** Only active categories. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** Filter by platform slug (e.g. 'instagram'). */
    public function scopeForPlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }

    /** Filter by service type (e.g. 'followers'). */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Return platform+type pairs that are actually used by active services.
     * Used to build filter dropdowns without showing empty options.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function activePlatforms(): \Illuminate\Support\Collection
    {
        return static::active()
            ->whereNotNull('platform')
            ->select('platform')
            ->distinct()
            ->orderBy('platform')
            ->pluck('platform');
    }

    public static function activeTypes(): \Illuminate\Support\Collection
    {
        return static::active()
            ->whereNotNull('type')
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');
    }
}
