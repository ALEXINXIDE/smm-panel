@extends('layouts.app')
@section('title', 'Order #' . $order->id)
@section('page-title', 'Order #' . $order->id)

@section('content')
<div class="max-w-2xl mx-auto">

<div class="glass-card rounded-xl p-md mb-5 fade-up">
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="font-h2 text-on-surface" style="font-size:24px">Order #{{ $order->id }}</h2>
            <p class="text-outline text-sm mt-1">Placed {{ $order->created_at->format('d M Y, H:i') }}</p>
        </div>
        @php
            $s  = strtolower($order->status);
            $sc = match(true){
                $s==='completed'                       =>'bg-tertiary/10 text-tertiary border-tertiary/30 shadow-[0_0_12px_rgba(78,222,163,0.2)]',
                in_array($s,['in progress','processing'])=>'bg-primary/10 text-primary border-primary/30 animate-pulse',
                in_array($s,['cancelled','refunded'])  =>'bg-error/10 text-error border-error/30',
                $s==='partial'                         =>'bg-secondary/10 text-secondary border-secondary/30',
                default                                =>'bg-surface-container-highest text-outline border-outline/30',
            };
        @endphp
        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full border text-xs font-bold uppercase tracking-wider {{ $sc }}">
            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
            {{ ucfirst($order->status) }}
        </span>
    </div>

    {{-- Progress bar --}}
    @php
        $progress = match(true) {
            $s === 'completed'    => 100,
            $s === 'partial'      => round((($order->quantity - $order->remains) / max($order->quantity, 1)) * 100),
            in_array($s,['in progress','processing']) => max(10, round((($order->quantity - $order->remains) / max($order->quantity, 1)) * 100)),
            default               => 0,
        };
    @endphp
    <div class="mb-6">
        <div class="flex justify-between text-xs text-outline mb-2">
            <span>Progress</span>
            <span>{{ $progress }}%</span>
        </div>
        <div class="w-full bg-surface-container-low h-2 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-1000
                {{ $s === 'completed' ? 'bg-tertiary' : ($s === 'partial' ? 'bg-secondary' : 'bg-gradient-primary') }}"
                style="width:{{ $progress }}%"></div>
        </div>
        @if($order->remains > 0 && $s !== 'completed')
        <p class="text-xs text-outline mt-1">{{ number_format($order->remains) }} remaining of {{ number_format($order->quantity) }}</p>
        @endif
    </div>

    {{-- Details --}}
    <div class="space-y-0">
        @foreach([
            ['Service',    $order->service->name ?? 'N/A'],
            ['Link',       $order->link],
            ['Quantity',   number_format($order->quantity)],
            ['Remains',    number_format($order->remains ?? 0)],
            ['Total USD',  '$' . number_format($order->total, 4)],
            ['Total PKR',  '₨' . number_format($order->total * session('usd_pkr_rate',280), 0)],
            ['API Order ID', $order->api_order_id ?? 'N/A'],
        ] as [$label, $value])
        <div class="flex justify-between items-center py-3 border-b border-outline-variant/20">
            <span class="text-outline text-sm">{{ $label }}</span>
            <span class="text-on-surface text-sm font-medium text-right max-w-[55%] truncate">{{ $value }}</span>
        </div>
        @endforeach
    </div>
</div>

<div class="flex gap-3">
    <a href="{{ route('orders.index') }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-outline-variant/40 text-outline hover:text-on-surface hover:bg-white/5 transition-all text-sm font-semibold">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> All Orders
    </a>
    <a href="{{ route('orders.create') }}" class="flex-1 bg-gradient-primary text-white font-semibold py-3 rounded-xl neon-glow-primary hover:brightness-110 transition-all text-sm flex items-center justify-center gap-2">
        <span class="material-symbols-outlined text-[18px]">add</span> New Order
    </a>
</div>

</div>
@endsection
