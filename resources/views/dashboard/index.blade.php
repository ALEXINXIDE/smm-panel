@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Welcome Hero --}}
<div class="glass-card rounded-xl p-md relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6 mb-gutter fade-up">
    <div class="absolute inset-0 z-0 opacity-10 pointer-events-none" style="background:linear-gradient(135deg,#4d8eff22,#571bc122);"></div>
    <div class="relative z-10 space-y-1">
        <h2 class="font-h2 text-h2 text-on-surface">Welcome back, <span class="text-primary neon-text-primary">{{ auth()->user()->name }}</span></h2>
        <p class="font-body-md text-body-md text-on-surface-variant">Your command center is active. All systems operational.</p>
    </div>
    <div class="relative z-10 flex gap-3">
        <a href="{{ route('orders.create') }}" class="bg-gradient-primary text-white font-semibold py-2 px-6 rounded-lg neon-glow-primary hover:brightness-110 transition-all flex items-center gap-2 text-sm">
            <span class="material-symbols-outlined text-[18px]">add</span> New Order
        </a>
        <a href="{{ route('funds.index') }}" class="glass-card text-on-surface font-semibold py-2 px-5 rounded-lg hover:bg-white/5 transition-all flex items-center gap-2 text-sm border border-outline-variant/40">
            <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span> Add Funds
        </a>
    </div>
</div>

{{-- Bento Grid Metrics --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter mb-gutter">

    {{-- Balance --}}
    <div class="glass-card rounded-xl p-sm flex flex-col justify-between relative overflow-hidden group fade-up">
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary/80 shadow-[0_0_10px_rgba(173,198,255,0.8)]"></div>
        <div class="flex justify-between items-start mb-4 pl-3">
            <span class="font-body-sm text-body-sm text-outline font-medium">Available Balance</span>
            <div class="p-1.5 rounded-md bg-surface-container-high border border-outline-variant">
                <span class="material-symbols-outlined text-primary text-[20px]">account_balance_wallet</span>
            </div>
        </div>
        <div class="pl-3">
            <span class="font-h1 text-h1 text-on-surface neon-text-primary" style="font-size:32px">${{ number_format($balance, 2) }}</span>
            <p class="text-xs text-outline mt-1">₨{{ number_format($balance * session('usd_pkr_rate',280), 0) }} PKR</p>
        </div>
    </div>

    {{-- Total Orders --}}
    <div class="glass-card rounded-xl p-sm flex flex-col justify-between group fade-up">
        <div class="flex justify-between items-start mb-4">
            <span class="font-body-sm text-body-sm text-outline font-medium">Total Orders</span>
            <div class="p-1.5 rounded-md bg-surface-container-high border border-outline-variant">
                <span class="material-symbols-outlined text-tertiary text-[20px]">shopping_cart</span>
            </div>
        </div>
        <div>
            <span class="font-h1 text-on-surface" style="font-size:36px">{{ number_format($total_orders) }}</span>
            <div class="flex items-center gap-1 mt-1 text-tertiary text-xs">
                <span class="material-symbols-outlined text-[14px]">trending_up</span>
                <span>{{ $orders_this_week }} this week</span>
            </div>
        </div>
    </div>

    {{-- Pending Orders --}}
    <div class="glass-card rounded-xl p-sm flex flex-col justify-between group fade-up">
        <div class="flex justify-between items-start mb-4">
            <span class="font-body-sm text-body-sm text-outline font-medium">Pending Orders</span>
            <div class="p-1.5 rounded-md bg-surface-container-high border border-outline-variant">
                <span class="material-symbols-outlined text-[#fcd34d] text-[20px]">hourglass_empty</span>
            </div>
        </div>
        <div>
            <span class="font-h1 text-on-surface {{ $pending_orders > 0 ? 'text-[#fcd34d]' : '' }}" style="font-size:36px">{{ $pending_orders }}</span>
            <div class="flex items-center gap-1 mt-1 text-outline text-xs">
                @if($pending_orders > 0)
                <span class="text-[#fcd34d]">Requires attention</span>
                @else
                <span class="text-tertiary">All clear</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Success Rate --}}
    <div class="glass-card rounded-xl p-sm flex flex-col justify-between group fade-up">
        <div class="flex justify-between items-start mb-4">
            <span class="font-body-sm text-body-sm text-outline font-medium">Success Rate</span>
            <div class="p-1.5 rounded-md bg-surface-container-high border border-outline-variant">
                <span class="material-symbols-outlined text-secondary text-[20px]">bolt</span>
            </div>
        </div>
        <div>
            <span class="font-h1 text-on-surface" style="font-size:36px">{{ $success_rate }}%</span>
            <div class="w-full bg-surface-container-low h-1 mt-3 rounded-full overflow-hidden">
                <div class="bg-secondary h-full rounded-full" style="width:{{ $success_rate }}%"></div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Order + Recent Activity --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">

    {{-- Quick Order Widget --}}
    <div class="lg:col-span-1 glass-card rounded-xl p-md flex flex-col h-full fade-up">
        <h3 class="font-h3 text-h3 text-on-surface mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">rocket_launch</span> Quick Order
        </h3>
        <form method="POST" action="{{ route('orders.store') }}" class="space-y-4 flex-1 flex flex-col" id="quick-order-form">
            @csrf
            <div class="space-y-1">
                <label class="font-label-caps text-label-caps text-outline">Category</label>
                <div class="relative">
                    <select name="category_id" id="qo-category" class="w-full glass-input py-2 pl-3 pr-10 appearance-none font-body-sm text-body-sm bg-transparent" onchange="loadServices(this.value)">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2 top-2.5 text-outline pointer-events-none">expand_more</span>
                </div>
            </div>
            <div class="space-y-1">
                <label class="font-label-caps text-label-caps text-outline">Service</label>
                <div class="relative">
                    <select name="service_id" id="qo-service" class="w-full glass-input py-2 pl-3 pr-10 appearance-none font-body-sm text-body-sm bg-transparent" onchange="updatePrice()">
                        <option value="">Select Service</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2 top-2.5 text-outline pointer-events-none">expand_more</span>
                </div>
            </div>
            <div class="space-y-1">
                <label class="font-label-caps text-label-caps text-outline">Link / Target</label>
                <input class="w-full glass-input py-2 px-3 font-body-sm text-body-sm placeholder:text-outline/50 bg-transparent" placeholder="https://..." type="url" name="link" required/>
            </div>
            <div class="space-y-1">
                <label class="font-label-caps text-label-caps text-outline">Quantity</label>
                <input class="w-full glass-input py-2 px-3 font-body-sm text-body-sm bg-transparent" placeholder="1000" type="number" name="quantity" id="qo-qty" min="1" required oninput="updatePrice()"/>
            </div>
            <div class="mt-auto pt-4 flex flex-col gap-3">
                <div class="flex justify-between items-center bg-surface-container-low p-3 rounded-lg border border-outline-variant/30">
                    <div>
                        <span class="font-body-sm text-body-sm text-on-surface-variant">Estimated Cost</span>
                        <p class="text-xs text-outline" id="qo-pkr">₨0</p>
                    </div>
                    <span class="font-h3 text-[20px] text-on-surface font-semibold" id="qo-price">$0.0000</span>
                </div>
                <button type="submit" class="w-full bg-gradient-primary text-white font-semibold py-3 rounded-lg neon-glow-primary hover:brightness-110 transition-all text-center text-sm">
                    Submit Order
                </button>
            </div>
        </form>
    </div>

    {{-- Recent Orders Table --}}
    <div class="lg:col-span-2 glass-card rounded-xl p-md flex flex-col fade-up">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-h3 text-h3 text-on-surface">Recent Activity</h3>
            <a class="font-body-sm text-body-sm text-primary hover:text-primary-fixed transition-colors flex items-center gap-1" href="{{ route('orders.index') }}">
                View All <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left font-body-sm text-body-sm border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/30 text-outline font-label-caps text-label-caps">
                        <th class="pb-3 font-normal">ID</th>
                        <th class="pb-3 font-normal">Service</th>
                        <th class="pb-3 font-normal">Link</th>
                        <th class="pb-3 font-normal text-right">Qty</th>
                        <th class="pb-3 font-normal text-right">Cost</th>
                        <th class="pb-3 font-normal text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="text-on-surface-variant">
                    @forelse($recent_orders as $order)
                    @php
                        $s = strtolower($order->status ?? 'pending');
                        $statusClass = match(true) {
                            in_array($s,['completed']) => 'bg-tertiary/10 text-tertiary border-tertiary/30 shadow-[0_0_8px_rgba(78,222,163,0.2)]',
                            in_array($s,['in progress','processing']) => 'bg-primary/10 text-primary border-primary/30 shadow-[0_0_8px_rgba(173,198,255,0.2)] animate-pulse',
                            in_array($s,['cancelled','refunded']) => 'bg-error/10 text-error border-error/30 shadow-[0_0_8px_rgba(255,180,171,0.2)]',
                            default => 'bg-surface-container-highest text-outline border-outline/30',
                        };
                    @endphp
                    <tr class="border-b border-surface-container-high hover:bg-white/5 transition-colors">
                        <td class="py-3 font-mono text-outline text-xs">#{{ $order->id }}</td>
                        <td class="py-3 text-on-surface max-w-[120px] truncate">{{ $order->service->name ?? 'N/A' }}</td>
                        <td class="py-3 text-outline truncate max-w-[100px] text-xs">{{ $order->link }}</td>
                        <td class="py-3 text-right">{{ number_format($order->quantity) }}</td>
                        <td class="py-3 text-right text-primary font-semibold">${{ number_format($order->total, 4) }}</td>
                        <td class="py-3 text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded border text-[10px] font-bold uppercase tracking-wider {{ $statusClass }}">
                                {{ ucfirst($order->status ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-12 text-center text-outline">
                        <span class="material-symbols-outlined text-[36px] block mb-2 opacity-30">shopping_cart</span>
                        No orders yet — <a href="{{ route('orders.create') }}" class="text-primary hover:underline">place your first order</a>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const PKR = {{ session('usd_pkr_rate', 280) }};
const SERVICES_URL = '{{ route("orders.services_by_category") }}';
let cachedServices = {};

function loadServices(catId) {
    const sel = document.getElementById('qo-service');
    sel.innerHTML = '<option value="">Loading...</option>';
    sel.disabled = true;

    if (cachedServices[catId]) {
        renderServices(sel, cachedServices[catId]);
        return;
    }

    fetch(SERVICES_URL + '?category_id=' + catId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(list => {
        cachedServices[catId] = list;
        renderServices(sel, list);
    })
    .catch(() => {
        sel.innerHTML = '<option value="">Failed to load</option>';
        sel.disabled = false;
    });
}

function renderServices(sel, list) {
    sel.innerHTML = '<option value="">Select Service</option>';
    list.forEach(s => {
        const o = document.createElement('option');
        o.value = s.id;
        o.dataset.rate = s.rate;
        o.dataset.min  = s.min;
        o.dataset.max  = s.max;
        o.textContent  = s.name + ' — $' + parseFloat(s.rate).toFixed(4) + '/1K';
        sel.appendChild(o);
    });
    sel.disabled = false;
    updatePrice();
}

function updatePrice() {
    const sel = document.getElementById('qo-service');
    const opt = sel.options[sel.selectedIndex];
    const qty = parseInt(document.getElementById('qo-qty').value) || 0;
    const rate = parseFloat(opt?.dataset?.rate || 0);
    const total = (qty / 1000) * rate;
    document.getElementById('qo-price').textContent = '$' + total.toFixed(4);
    document.getElementById('qo-pkr').textContent   = '₨' + Math.round(total * PKR).toLocaleString();
}
</script>
@endsection
