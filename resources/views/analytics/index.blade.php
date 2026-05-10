@extends('layouts.app')
@section('title', 'Analytics & Insights')
@section('page-title', 'Analytics & Insights')

@section('content')

{{-- Top stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-gutter mb-gutter fade-up">
    <div class="glass-card rounded-xl p-sm">
        <p class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-widest mb-3">Total Spent</p>
        <p class="font-h1 text-on-surface neon-text-primary" style="font-size:36px">${{ number_format($totalSpent, 2) }}</p>
        <p class="text-outline text-xs mt-1">₨{{ number_format($totalSpent * session('usd_pkr_rate',280), 0) }}</p>
    </div>
    <div class="glass-card rounded-xl p-sm">
        <p class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-widest mb-3">Total Orders</p>
        <p class="font-h1 text-on-surface" style="font-size:36px">{{ number_format($totalOrders) }}</p>
        <div class="flex items-center gap-1 mt-1 text-tertiary text-xs">
            <span class="material-symbols-outlined text-[14px]">trending_up</span>
            <span>{{ $ordersThisMonth }} this month</span>
        </div>
    </div>
    <div class="glass-card rounded-xl p-sm">
        <p class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-widest mb-3">Best Service</p>
        <p class="text-on-surface font-semibold text-lg leading-snug">{{ $bestService ?? 'N/A' }}</p>
        <p class="text-outline text-xs mt-1">Most ordered</p>
    </div>
</div>

{{-- Charts row --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter mb-gutter fade-up">

    {{-- Spending over time --}}
    <div class="glass-card rounded-xl p-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-h3 text-h3 text-on-surface" style="font-size:18px">Spending (Last 30 Days)</h3>
            <span class="text-xs text-outline bg-surface-container px-2 py-1 rounded-lg">USD</span>
        </div>
        <canvas id="spending-chart" height="180"></canvas>
    </div>

    {{-- Orders by status --}}
    <div class="glass-card rounded-xl p-md">
        <h3 class="font-h3 text-h3 text-on-surface mb-4" style="font-size:18px">Orders by Status</h3>
        <div class="flex items-center justify-center">
            <canvas id="status-chart" height="180" width="180"></canvas>
        </div>
        <div class="grid grid-cols-2 gap-2 mt-4">
            @foreach([['Completed','#4edea3',$completed],['Pending','#fcd34d',$pending],['Processing','#adc6ff',$processing],['Cancelled','#ffb4ab',$cancelled]] as [$label,$color,$count])
            <div class="flex items-center gap-2">
                <div style="width:10px;height:10px;border-radius:2px;background:{{$color}};flex-shrink:0;box-shadow:0 0 6px {{$color}}"></div>
                <span class="text-xs text-outline flex-1">{{$label}}</span>
                <span class="text-xs font-bold text-on-surface">{{$count}}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Top services --}}
<div class="glass-card rounded-xl p-md fade-up">
    <h3 class="font-h3 text-h3 text-on-surface mb-5" style="font-size:18px">Top Services by Spend</h3>
    <div class="space-y-3">
        @forelse($topServices as $svc)
        @php $pct = $totalSpent > 0 ? ($svc->total_spent / $totalSpent) * 100 : 0; @endphp
        <div>
            <div class="flex justify-between items-center mb-1">
                <span class="text-on-surface text-sm font-medium">{{ $svc->service_name }}</span>
                <div class="text-right">
                    <span class="text-primary font-bold text-sm">${{ number_format($svc->total_spent, 2) }}</span>
                    <span class="text-outline text-xs ml-2">{{ $svc->order_count }} orders</span>
                </div>
            </div>
            <div class="w-full bg-surface-container-low h-1.5 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-gradient-primary" style="width:{{ min($pct,100) }}%;transition:width 1s ease"></div>
            </div>
        </div>
        @empty
        <p class="text-center text-outline py-8">No order data yet</p>
        @endforelse
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
// Spending chart
new Chart(document.getElementById('spending-chart'),{
    type:'line',
    data:{
        labels:@json($chartLabels),
        datasets:[{
            label:'Spend ($)',
            data:@json($chartData),
            borderColor:'#adc6ff',
            backgroundColor:'rgba(173,198,255,0.08)',
            borderWidth:2,
            pointRadius:3,
            pointBackgroundColor:'#adc6ff',
            fill:true,
            tension:0.4
        }]
    },
    options:{
        responsive:true,maintainAspectRatio:false,
        plugins:{legend:{display:false},tooltip:{backgroundColor:'rgba(23,31,51,0.95)',borderColor:'rgba(173,198,255,0.2)',borderWidth:1,titleColor:'#dae2fd',bodyColor:'#8c909f'}},
        scales:{
            x:{grid:{color:'rgba(66,71,84,0.3)'},ticks:{color:'#8c909f',font:{size:11}}},
            y:{grid:{color:'rgba(66,71,84,0.3)'},ticks:{color:'#8c909f',font:{size:11},callback:v=>'$'+v}}
        }
    }
});

// Status donut
new Chart(document.getElementById('status-chart'),{
    type:'doughnut',
    data:{
        labels:['Completed','Pending','Processing','Cancelled'],
        datasets:[{
            data:[{{ $completed }},{{ $pending }},{{ $processing }},{{ $cancelled }}],
            backgroundColor:['rgba(78,222,163,0.8)','rgba(252,211,77,0.8)','rgba(173,198,255,0.8)','rgba(255,180,171,0.8)'],
            borderColor:['#4edea3','#fcd34d','#adc6ff','#ffb4ab'],
            borderWidth:1
        }]
    },
    options:{
        cutout:'70%',
        plugins:{legend:{display:false},tooltip:{backgroundColor:'rgba(23,31,51,0.95)',borderColor:'rgba(173,198,255,0.2)',borderWidth:1,titleColor:'#dae2fd',bodyColor:'#8c909f'}}
    }
});
</script>
@endsection
