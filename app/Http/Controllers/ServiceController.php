<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ServiceFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    public function __construct(private ServiceFilter $filter)
    {
        $this->middleware('auth');
    }

    /**
     * GET /services
     *
     * Supports query params: platform, type, q (search), sort, page.
     * Results are paginated at 20 per page (cheapest first by default).
     */
    public function index(Request $request)
    {
        // ── Categories (cached 10 min) ────────────────────────────────────
        // We only cache the full list used to build dropdowns.
        // The filtered service list is NOT cached because it is user-specific
        // (search terms, sort prefs) and changes frequently.
        $categories = Cache::remember('active_categories', 600, fn () =>
            Category::active()
                ->select('id', 'name', 'platform', 'type')
                ->orderBy('platform')
                ->orderBy('type')
                ->get()
        );

        // Distinct platform/type values for filter dropdowns
        // These are derived from the cached category list — no extra queries.
        $platforms = $categories->pluck('platform')->filter()->unique()->sort()->values();
        $types     = $categories->pluck('type')->filter()->unique()->sort()->values();

        // ── Paginated, filtered services ──────────────────────────────────
        $services = $this->filter->paginate($request);

        return view('services.index', compact(
            'services',
            'categories',
            'platforms',
            'types'
        ));
    }
}
