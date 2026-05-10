<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\ProviderApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * OrderController
 *
 * FIXES:
 * - Renamed method to getServicesByCategory to match web.php route name
 * - Creates a Transaction (deduction) record for every order placed
 * - Validates service status and quantity
 */
class OrderController extends Controller
{
    public function __construct(protected \App\Services\OrderService $orderService)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $orders = Order::with('service')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'icon', 'color']);

        return view('orders.create', compact('categories'));
    }

    /**
     * FIXED: This method name now matches the route: 
     * Route::get('services-by-category', [OrderController::class, 'getServicesByCategory'])->name('services_by_category');
     */
    public function getServicesByCategory(Request $request)
    {
        $categoryId = $request->integer('category_id');

        if (!$categoryId) {
            return response()->json([]);
        }

        $services = Cache::remember(
            "services_cat_{$categoryId}",
            300,
            fn () => Service::where('status', 'active')
                ->where('category_id', $categoryId)
                ->orderBy('name')
                ->get(['id', 'name', 'rate', 'min', 'max', 'description', 'min_time', 'max_time'])
        );

        return response()->json($services);
    }

    public function store(\App\Http\Requests\StoreOrderRequest $request)
    {
        $validated = $request->validated();

        if ($this->orderService->isDuplicateOrder(Auth::id(), $validated['service_id'], $validated['link'])) {
            return back()->withErrors(['error' => 'You already placed an identical order a moment ago. Please wait 60 seconds.']);
        }

        try {
            $order = $this->orderService->createOrder($validated);

            Log::info('Order placed', [
                'order_id'   => $order->id,
                'user_id'    => Auth::id(),
                'service_id' => $validated['service_id'],
                'total'      => $order->total,
                'ip'         => $request->ip(),
            ]);

            return redirect()->route('orders.show', $order->id)
                ->with('success', "Order #{$order->id} placed successfully!");

        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $order->load('service');

        return view('orders.show', compact('order'));
    }
}
