@extends('layouts.app')
@section('title', 'Services & Pricing')
@section('page-title', 'Services & Pricing')

@section('content')

{{-- ── Page header ──────────────────────────────────────────────────────── --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 fade-up">
    <div>
        <h1 class="font-h1 text-on-surface" style="font-size:32px">Services &amp; Pricing</h1>
        <p class="text-on-surface-variant text-sm mt-1">
            {{ $services->total() }} services found
        </p>
    </div>
</div>

{{-- ── Filter bar ───────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('services.index') }}"
      id="filter-form"
      class="glass-card rounded-xl p-4 mb-6 fade-up">

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

        {{-- Platform dropdown --}}
        <div>
            <label class="block text-xs text-outline mb-1.5 uppercase tracking-wider">Platform</label>
            <select name="platform"
                    class="w-full glass-input bg-transparent py-2.5 px-3 rounded-lg border border-outline-variant/40 focus:border-primary text-sm transition-colors"
                    onchange="this.form.submit()">
                <option value="">All Platforms</option>
                @foreach($platforms as $p)
                    <option value="{{ $p }}" @selected(request('platform') === $p)>
                        {{ ucfirst($p) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Type dropdown --}}
        <div>
            <label class="block text-xs text-outline mb-1.5 uppercase tracking-wider">Type</label>
            <select name="type"
                    class="w-full glass-input bg-transparent py-2.5 px-3 rounded-lg border border-outline-variant/40 focus:border-primary text-sm transition-colors"
                    onchange="this.form.submit()">
                <option value="">All Types</option>
                @foreach($types as $t)
                    <option value="{{ $t }}" @selected(request('type') === $t)>
                        {{ ucfirst($t) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Search --}}
        <div>
            <label class="block text-xs text-outline mb-1.5 uppercase tracking-wider">Search</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-outline text-[18px] pointer-events-none">search</span>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Service name…"
                       class="w-full glass-input bg-transparent pl-10 pr-4 py-2.5 rounded-lg border border-outline-variant/40 focus:border-primary text-sm transition-colors">
            </div>
        </div>

        {{-- Sort --}}
        <div>
            <label class="block text-xs text-outline mb-1.5 uppercase tracking-wider">Sort</label>
            <select name="sort"
                    class="w-full glass-input bg-transparent py-2.5 px-3 rounded-lg border border-outline-variant/40 focus:border-primary text-sm transition-colors"
                    onchange="this.form.submit()">
                <option value="price" @selected(request('sort','price') === 'price')>Cheapest First</option>
                <option value="name"  @selected(request('sort') === 'name')>Name A–Z</option>
            </select>
        </div>

    </div>

    {{-- Active filter badges + clear --}}
    @if(request()->hasAny(['platform','type','q','sort']))
    <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-outline-variant/20">
        @if(request('platform'))
            <span class="inline-flex items-center gap-1 bg-primary/10 text-primary border border-primary/30 px-3 py-1 rounded-full text-xs font-semibold">
                {{ ucfirst(request('platform')) }}
                <a href="{{ request()->fullUrlWithoutQuery('platform') }}" class="hover:text-error ml-1">✕</a>
            </span>
        @endif
        @if(request('type'))
            <span class="inline-flex items-center gap-1 bg-secondary/10 text-secondary border border-secondary/30 px-3 py-1 rounded-full text-xs font-semibold">
                {{ ucfirst(request('type')) }}
                <a href="{{ request()->fullUrlWithoutQuery('type') }}" class="hover:text-error ml-1">✕</a>
            </span>
        @endif
        @if(request('q'))
            <span class="inline-flex items-center gap-1 bg-tertiary/10 text-tertiary border border-tertiary/30 px-3 py-1 rounded-full text-xs font-semibold">
                "{{ request('q') }}"
                <a href="{{ request()->fullUrlWithoutQuery('q') }}" class="hover:text-error ml-1">✕</a>
            </span>
        @endif
        <a href="{{ route('services.index') }}"
           class="inline-flex items-center gap-1 text-outline hover:text-on-surface text-xs underline ml-auto">
            Clear all filters
        </a>
    </div>
    @endif

</form>

{{-- ── Services table ───────────────────────────────────────────────────── --}}
<div class="glass-card rounded-xl overflow-hidden fade-up">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-outline-variant/30">
                    <th class="px-5 py-3 font-label-caps text-label-caps text-outline font-normal">Service</th>
                    <th class="px-5 py-3 font-label-caps text-label-caps text-outline font-normal">Platform / Type</th>
                    <th class="px-5 py-3 font-label-caps text-label-caps text-outline font-normal text-right">Rate / 1K</th>
                    <th class="px-5 py-3 font-label-caps text-label-caps text-outline font-normal text-right hidden sm:table-cell">PKR / 1K</th>
                    <th class="px-5 py-3 font-label-caps text-label-caps text-outline font-normal text-right hidden md:table-cell">Min</th>
                    <th class="px-5 py-3 font-label-caps text-label-caps text-outline font-normal text-right hidden md:table-cell">Max</th>
                    <th class="px-5 py-3 font-label-caps text-label-caps text-outline font-normal text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $svc)
                <tr class="border-b border-surface-container-high hover:bg-white/5 transition-colors">

                    {{-- Name + description --}}
                    <td class="px-5 py-4">
                        <p class="text-on-surface font-medium text-sm">{{ $svc->name }}</p>
                        @if($svc->description)
                        <p class="text-outline text-xs mt-0.5 max-w-xs truncate">{{ $svc->description }}</p>
                        @endif
                    </td>

                    {{-- Platform / Type badges --}}
                    <td class="px-5 py-4">
                        @if($svc->category)
                            @if($svc->category->platform)
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20 mr-1">
                                {{ $svc->category->platform }}
                            </span>
                            @endif
                            @if($svc->category->type)
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-secondary/10 text-secondary border border-secondary/20">
                                {{ $svc->category->type }}
                            </span>
                            @endif
                        @else
                            <span class="text-outline text-xs">—</span>
                        @endif
                    </td>

                    {{-- Rate USD --}}
                    <td class="px-5 py-4 text-right">
                        <span class="text-primary font-bold font-inter">${{ number_format($svc->rate, 4) }}</span>
                    </td>

                    {{-- Rate PKR --}}
                    <td class="px-5 py-4 text-right text-tertiary text-sm hidden sm:table-cell">
                        ₨{{ number_format($svc->rate * session('usd_pkr_rate', 280), 1) }}
                    </td>

                    <td class="px-5 py-4 text-right text-outline text-sm hidden md:table-cell">{{ number_format($svc->min) }}</td>
                    <td class="px-5 py-4 text-right text-outline text-sm hidden md:table-cell">{{ number_format($svc->max) }}</td>

                    {{-- Order CTA --}}
                    <td class="px-5 py-4 text-center">
                        <a href="{{ route('orders.create') }}?service={{ $svc->id }}"
                           class="inline-flex items-center gap-1 bg-gradient-primary text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:brightness-110 transition-all neon-glow-primary">
                            <span class="material-symbols-outlined text-[14px]">add</span> Order
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center text-outline">
                        <span class="material-symbols-outlined text-[48px] block mb-3 opacity-20">inventory_2</span>
                        <p class="mb-2">No services match your filters.</p>
                        <a href="{{ route('services.index') }}" class="text-primary text-sm hover:underline">Clear filters →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($services->hasPages())
    <div class="px-5 py-4 border-t border-outline-variant/30 flex items-center justify-between flex-wrap gap-3">
        <p class="text-xs text-outline">
            Showing {{ $services->firstItem() }}–{{ $services->lastItem() }} of {{ $services->total() }}
        </p>
        <div class="flex gap-1">
            @if($services->onFirstPage())
                <span class="px-3 py-1.5 text-xs text-outline border border-outline-variant/30 rounded-lg opacity-40">← Prev</span>
            @else
                <a href="{{ $services->previousPageUrl() }}"
                   class="px-3 py-1.5 text-xs text-on-surface border border-outline-variant/30 rounded-lg hover:bg-white/5 transition-colors">← Prev</a>
            @endif

            {{-- Page numbers (condensed) --}}
            @foreach($services->getUrlRange(max(1,$services->currentPage()-2), min($services->lastPage(),$services->currentPage()+2)) as $page => $url)
                @if($page === $services->currentPage())
                    <span class="px-3 py-1.5 text-xs bg-primary/20 text-primary border border-primary/40 rounded-lg font-bold">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-1.5 text-xs text-on-surface border border-outline-variant/30 rounded-lg hover:bg-white/5 transition-colors">{{ $page }}</a>
                @endif
            @endforeach

            @if($services->hasMorePages())
                <a href="{{ $services->nextPageUrl() }}"
                   class="px-3 py-1.5 text-xs text-on-surface border border-outline-variant/30 rounded-lg hover:bg-white/5 transition-colors">Next →</a>
            @else
                <span class="px-3 py-1.5 text-xs text-outline border border-outline-variant/30 rounded-lg opacity-40">Next →</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
// Auto-submit on search input with debounce to avoid hammering the server
(function () {
    const searchInput = document.querySelector('input[name="q"]');
    if (!searchInput) return;
    let timer;
    searchInput.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => searchInput.closest('form').submit(), 400);
    });
})();
</script>
@endsection
