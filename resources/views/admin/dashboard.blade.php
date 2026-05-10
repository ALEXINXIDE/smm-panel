@extends('layouts.app')
@section('title', 'Command Center')
@section('page-title', 'Command Center')

@section('content')

{{-- Hero stats --}}
<div class="glass-card rounded-xl p-md mb-gutter relative overflow-hidden fade-up">
    <div class="absolute inset-0 opacity-10" style="background:linear-gradient(135deg,#4d8eff22,#571bc133)"></div>
    <div class="relative z-10">
        <h2 class="font-h2 text-white mb-1" style="font-size:22px">Admin Command Center</h2>
        <p class="text-on-surface-variant text-sm mb-6">Live platform overview — {{ now()->format('D, d M Y H:i') }}</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-outline text-xs font-label-caps uppercase tracking-widest mb-1">Total Revenue</p>
                <p class="font-h1 text-white tracking-tight" style="font-size:28px">${{ number_format($total_revenue, 2) }}</p>
            </div>
            <div>
                <p class="text-outline text-xs font-label-caps uppercase tracking-widest mb-1">Total Orders</p>
                <p class="font-h1 text-white tracking-tight" style="font-size:28px">{{ number_format($total_orders) }}</p>
            </div>
            <div>
                <p class="text-outline text-xs font-label-caps uppercase tracking-widest mb-1">Active Users</p>
                <p class="font-h1 text-white tracking-tight" style="font-size:28px">{{ number_format($active_users) }}</p>
            </div>
            <div>
                <p class="text-outline text-xs font-label-caps uppercase tracking-widest mb-1">Pending Orders</p>
                <p class="font-h1 tracking-tight {{ $pending_orders > 0 ? 'text-[#fcd34d]' : 'text-white' }}" style="font-size:28px">{{ $pending_orders }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Main grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mb-gutter">

    {{-- API Providers --}}
    <div class="glass-card rounded-xl p-md fade-up">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-h3 text-on-surface" style="font-size:16px">API Providers</h3>
            <a href="{{ route('admin.providers.create') }}" class="bg-gradient-primary text-white px-3 py-1 rounded-lg text-xs font-semibold hover:brightness-110 transition-all">+ Add</a>
        </div>
        <div class="space-y-3">
            @forelse($providers as $p)
            <div class="flex items-center justify-between p-3 bg-surface-container rounded-xl border border-outline-variant/20">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-gradient-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                        {{ strtoupper(substr($p->name,0,1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-on-surface text-sm font-medium truncate">{{ $p->name }}</p>
                        <p class="text-outline text-xs truncate">{{ $p->url }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                    <span class="w-2 h-2 rounded-full {{ $p->status === 'active' ? 'bg-tertiary' : 'bg-error' }}" style="box-shadow:0 0 5px {{ $p->status === 'active' ? '#4edea3' : '#ffb4ab' }}"></span>
                    <form method="POST" action="{{ route('admin.providers.sync', $p->id) }}">
                        @csrf
                        <button type="submit" class="text-outline hover:text-primary transition-colors text-xs font-semibold" title="Sync services">
                            <span class="material-symbols-outlined text-[16px]">sync</span>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-outline text-sm">
                <span class="material-symbols-outlined text-[36px] block mb-2 opacity-20">cloud_off</span>
                No providers yet. <a href="{{ route('admin.providers.create') }}" class="text-primary hover:underline">Add one</a>
            </div>
            @endforelse
        </div>

        {{-- WHERE TO PUT YOUR API KEYS --}}
        <div class="mt-4 p-3 bg-primary/5 border border-primary/20 rounded-xl">
            <p class="text-primary text-xs font-semibold mb-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">key</span> API Key Setup
            </p>
            <p class="text-outline text-xs leading-relaxed">
                Add your Peakerr / SMMWorld API keys here. Go to
                <a href="{{ route('admin.providers.create') }}" class="text-primary hover:underline font-semibold">Add Provider</a>
                and enter:<br>
                • <strong class="text-on-surface">URL:</strong> https://peakerr.com/api/v2<br>
                • <strong class="text-on-surface">API Key:</strong> from your provider dashboard<br>
                Then click Sync to import all services.
            </p>
        </div>
    </div>

    {{-- Recent orders --}}
    <div class="lg:col-span-2 glass-card rounded-xl p-md fade-up">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-h3 text-on-surface" style="font-size:16px">Recent Orders</h3>
            <a href="{{ route('admin.orders.index') }}" class="text-primary text-xs hover:underline flex items-center gap-1">
                View all <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b border-outline-variant/30">
                        <th class="pb-3 text-left font-label-caps text-label-caps text-outline font-normal">ID</th>
                        <th class="pb-3 text-left font-label-caps text-label-caps text-outline font-normal">User</th>
                        <th class="pb-3 text-left font-label-caps text-label-caps text-outline font-normal">Service</th>
                        <th class="pb-3 text-right font-label-caps text-label-caps text-outline font-normal">Total</th>
                        <th class="pb-3 text-right font-label-caps text-label-caps text-outline font-normal">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_orders as $order)
                    @php $s = strtolower($order->status ?? 'pending');
                    $sc = match(true){
                        in_array($s,['completed'])      => 'bg-tertiary/10 text-tertiary border-tertiary/30',
                        in_array($s,['in progress','processing']) => 'bg-primary/10 text-primary border-primary/30 animate-pulse',
                        in_array($s,['cancelled'])      => 'bg-error/10 text-error border-error/30',
                        default                         => 'bg-surface-container-highest text-outline border-outline/30',
                    }; @endphp
                    <tr class="border-b border-surface-container-high hover:bg-white/5 transition-colors">
                        <td class="py-3 font-mono text-outline text-xs">#{{ $order->id }}</td>
                        <td class="py-3 text-on-surface-variant text-xs">{{ $order->user->name ?? 'N/A' }}</td>
                        <td class="py-3 text-on-surface max-w-[140px] truncate text-xs">{{ $order->service->name ?? 'N/A' }}</td>
                        <td class="py-3 text-right text-primary font-semibold">${{ number_format($order->total,4) }}</td>
                        <td class="py-3 text-right">
                            <span class="inline-flex px-2 py-0.5 rounded border text-[10px] font-bold uppercase {{ $sc }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Users + quick actions --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter fade-up">

    {{-- Recent users --}}
    <div class="glass-card rounded-xl p-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-h3 text-on-surface" style="font-size:16px">Recent Users</h3>
            <a href="{{ route('admin.users.index') }}" class="text-primary text-xs hover:underline">View all →</a>
        </div>
        <div class="space-y-3">
            @foreach($recent_users as $user)
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-on-surface text-sm font-medium truncate">{{ $user->name }}</p>
                    <p class="text-outline text-xs truncate">{{ $user->email }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-tertiary text-xs font-semibold">${{ number_format($user->funds,2) }}</p>
                    <p class="text-outline text-[10px]">{{ $user->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Admin quick actions --}}
    <div class="glass-card rounded-xl p-md">
        <h3 class="font-h3 text-on-surface mb-4" style="font-size:16px">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-3">
            @foreach([
                ['Sync Services','sync',route('admin.sync.all'),'text-primary','POST'],
                ['Manage Services','list_alt',route('admin.services.index'),'text-tertiary','GET'],
                ['Manage Users','people',route('admin.users.index'),'text-secondary','GET'],
                ['Transactions','receipt_long',route('admin.transactions.index'),'text-[#fcd34d]','GET'],
                ['Open Tickets','confirmation_number',route('admin.tickets.index'),'text-error','GET'],
                ['Settings','settings',route('admin.settings'),'text-outline','GET'],
            ] as [$label,$icon,$url,$color,$method])
            @if($method === 'POST')
            <form method="POST" action="{{ $url }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 p-3 rounded-xl bg-surface-container border border-outline-variant/20 hover:border-outline-variant/50 hover:bg-white/5 transition-all text-sm">
                    <span class="material-symbols-outlined {{ $color }} text-[18px]">{{ $icon }}</span>
                    <span class="text-on-surface-variant text-xs font-medium">{{ $label }}</span>
                </button>
            </form>
            @else
            <a href="{{ $url }}" class="flex items-center gap-2 p-3 rounded-xl bg-surface-container border border-outline-variant/20 hover:border-outline-variant/50 hover:bg-white/5 transition-all text-sm">
                <span class="material-symbols-outlined {{ $color }} text-[18px]">{{ $icon }}</span>
                <span class="text-on-surface-variant text-xs font-medium">{{ $label }}</span>
            </a>
            @endif
            @endforeach
        </div>
    </div>
</div>
@endsection
