<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user         = Auth::user();
        $currentMonth = now()->month;
        $startOfWeek  = now()->startOfWeek()->toDateTimeString();

        // Single aggregated query — PostgreSQL compatible
        $stats = Order::where('user_id', $user->id)
            ->selectRaw("
                COUNT(*)                                                             AS total_orders,
                SUM(CASE WHEN status IN ('pending','in progress') THEN 1 ELSE 0 END) AS pending_orders,
                SUM(CASE WHEN status = 'in progress'  THEN 1 ELSE 0 END)            AS processing_orders,
                SUM(CASE WHEN status = 'completed'    THEN 1 ELSE 0 END)            AS completed_orders,
                SUM(CASE WHEN created_at >= ?         THEN 1 ELSE 0 END)            AS orders_this_week,
                SUM(CASE WHEN status = 'completed'
                         AND EXTRACT(MONTH FROM created_at) = ?
                         THEN total ELSE 0 END)                                     AS spent_month
            ", [$startOfWeek, $currentMonth])
            ->first();

        $total_orders      = (int)   ($stats->total_orders      ?? 0);
        $pending_orders    = (int)   ($stats->pending_orders     ?? 0);
        $processing_orders = (int)   ($stats->processing_orders  ?? 0);
        $completed_orders  = (int)   ($stats->completed_orders   ?? 0);
        $orders_this_week  = (int)   ($stats->orders_this_week   ?? 0);
        $spent_month       = (float) ($stats->spent_month        ?? 0);
        $balance           = $user->funds ?? 0;

        $success_rate = $total_orders > 0
            ? round(($completed_orders / $total_orders) * 100, 1)
            : 99.8;

        $recent_orders = Order::with('service:id,name')
            ->where('user_id', $user->id)
            ->latest()
            ->take(8)
            ->get(['id', 'service_id', 'status', 'total', 'quantity', 'created_at']);

        // Categories cached — used for quick-order widget on dashboard
        $categories = Cache::remember('active_categories', 600, fn () =>
            Category::where('status', 'active')->get(['id', 'name', 'icon', 'color'])
        );

        // DO NOT pass services here — 5,655 services in @json kills page load.
        // The quick-order widget fetches services via AJAX (same endpoint as /orders/new).

        return view('dashboard.index', compact(
            'balance',
            'total_orders',
            'pending_orders',
            'processing_orders',
            'completed_orders',
            'orders_this_week',
            'spent_month',
            'success_rate',
            'recent_orders',
            'categories'
        ));
    }
}
