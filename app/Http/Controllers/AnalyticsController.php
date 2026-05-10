<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $currentMonth = (int) now()->format('m');
        $currentYear  = (int) now()->format('Y');

        // Consolidated aggregate query — PostgreSQL compatible
        $stats = Order::where('user_id', $user->id)
            ->selectRaw("
                SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as total_spent,
                COUNT(*) as total_orders,
                COUNT(CASE WHEN EXTRACT(MONTH FROM created_at) = ? AND EXTRACT(YEAR FROM created_at) = ? THEN 1 END) as orders_this_month,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'in progress' THEN 1 END) as processing,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
            ", [$currentMonth, $currentYear])
            ->first();

        $totalSpent = $stats->total_spent ?? 0;
        $totalOrders = $stats->total_orders ?? 0;
        $ordersThisMonth = $stats->orders_this_month ?? 0;
        $completed = $stats->completed ?? 0;
        $pending = $stats->pending ?? 0;
        $processing = $stats->processing ?? 0;
        $cancelled = $stats->cancelled ?? 0;

        // Last 30 days spending chart
        $chartRaw = Order::where('user_id', $user->id)
            ->where('orders.status', 'completed')
            ->where('created_at', '>=', now()->subDays(29))
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $chartLabels = [];
        $chartData = [];

        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('M d');
            $chartData[] = round($chartRaw[$day]->total ?? 0, 4);
        }

        // Top services
        $topServices = Order::where('user_id', $user->id)
            ->where('orders.status', 'completed')
            ->join('services', 'orders.service_id', '=', 'services.id')
            ->selectRaw('services.name as service_name, SUM(orders.total) as total_spent, COUNT(*) as order_count')
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total_spent')
            ->take(8)
            ->get();

        $bestService = $topServices->first()?->service_name;

        return view('analytics.index', compact(
            'totalSpent',
            'totalOrders',
            'ordersThisMonth',
            'completed',
            'pending',
            'processing',
            'cancelled',
            'chartLabels',
            'chartData',
            'topServices',
            'bestService'
        ));
    }
}
