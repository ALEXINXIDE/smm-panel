<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * ServiceFilter
 * ─────────────────────────────────────────────────────────────────────────────
 * Centralises all service-listing query logic so both ServiceController and
 * future API endpoints share identical behaviour without duplicate code.
 * ─────────────────────────────────────────────────────────────────────────────
 */
class ServiceFilter
{
    private const PER_PAGE    = 20;
    private const SORT_VALUES = ['price', 'name'];

    /**
     * Build a paginated, filtered, sorted service list from query-string params.
     *
     * Supported params:
     *   platform  – e.g. instagram | tiktok | youtube
     *   type      – e.g. followers | likes | views
     *   q         – name search string
     *   sort      – price (default) | name
     *   page      – pagination (handled by Laravel)
     *
     * No N+1: category is eager-loaded once.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $platform = $request->input('platform');
        $type     = $request->input('type');
        $search   = $request->input('q');
        $sort     = in_array($request->input('sort'), self::SORT_VALUES, true)
                    ? $request->input('sort')
                    : 'price';

        return Service::with('category')           // eager-load, no N+1
            ->active()
            ->forPlatform($platform)
            ->ofType($type)
            ->search($search)
            ->sorted($sort)
            ->paginate(self::PER_PAGE)
            ->withQueryString();                    // preserve filters in page links
    }
}
