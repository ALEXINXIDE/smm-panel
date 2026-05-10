@extends('layouts.app')

@section('title', 'Services')
@section('page-title', 'Services')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Services</h1>
                <p class="text-on-surface-variant mt-1">Manage all API provider services</p>
            </div>
        </div>

        {{-- Alerts --}}
        @if ($errors->has('error'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-error bg-error/5">
                <p class="text-error font-medium">{{ $errors->first('error') }}</p>
            </div>
        @endif

        @if (session('success'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5">
                <p class="text-tertiary font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Filters --}}
        <div class="glass-card p-4 rounded-xl mb-6">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span class="text-on-surface font-medium">Sort by:</span>
                        <div class="flex gap-2">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_direction' => request('sort_by') === 'name' && request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('sort_by') === 'name' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                                Name {{ request('sort_by') === 'name' ? (request('sort_direction') === 'asc' ? '↑' : '↓') : '' }}
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'price', 'sort_direction' => request('sort_by') === 'price' && request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('sort_by') === 'price' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                                Price {{ request('sort_by') === 'price' ? (request('sort_direction') === 'asc' ? '↑' : '↓') : '' }}
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'delivery_time', 'sort_direction' => request('sort_by') === 'delivery_time' && request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('sort_by') === 'delivery_time' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                                Delivery Time {{ request('sort_by') === 'delivery_time' ? (request('sort_direction') === 'asc' ? '↑' : '↓') : '' }}
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-error text-on-error rounded-lg text-sm font-medium hover:bg-error/80 transition-colors">
                        Reset Filters
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-on-surface font-medium">Tier:</span>
                    <div class="flex gap-2">
                        <a href="{{ request()->fullUrlWithQuery(['tier' => '']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ !request('tier') || request('tier') === '' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                            All
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['tier' => 'economy']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('tier') === 'economy' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                            Economy
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['tier' => 'standard']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('tier') === 'standard' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                            Standard
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['tier' => 'premium']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('tier') === 'premium' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                            Premium
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Services Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container">
                            <th class="px-6 py-4 text-left">
                                @php
                                    $isActive = request('sort_by') === 'name';
                                    $direction = $isActive && request('sort_direction') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_direction' => $direction]) }}" class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest hover:text-on-surface transition-colors {{ $isActive ? 'text-primary' : '' }}">
                                    Service
                                    @if($isActive)
                                        <span class="material-symbols-outlined text-xs align-middle">{{ request('sort_direction') === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Provider</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                @php
                                    $isActive = request('sort_by') === 'price';
                                    $direction = $isActive && request('sort_direction') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'price', 'sort_direction' => $direction]) }}" class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest hover:text-on-surface transition-colors {{ $isActive ? 'text-primary' : '' }}">
                                    Price
                                    @if($isActive)
                                        <span class="material-symbols-outlined text-xs align-middle">{{ request('sort_direction') === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left">
                                @php
                                    $isActive = request('sort_by') === 'delivery_time';
                                    $direction = $isActive && request('sort_direction') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'delivery_time', 'sort_direction' => $direction]) }}" class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest hover:text-on-surface transition-colors {{ $isActive ? 'text-primary' : '' }}">
                                    Delivery Time
                                    @if($isActive)
                                        <span class="material-symbols-outlined text-xs align-middle">{{ request('sort_direction') === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Status</p>
                            </th>
                            <th class="px-6 py-4 text-right">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                            <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-medium">{{ $service->name }}</p>
                                    @if ($service->description)
                                        <p class="text-on-surface-variant text-sm mt-1">{{ Str::limit($service->description, 50) }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-gradient-primary flex items-center justify-center text-white font-bold text-xs">
                                            {{ strtoupper(substr($service->apiProvider->name, 0, 1)) }}
                                        </div>
                                        <p class="text-on-surface text-sm">{{ $service->apiProvider->name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-medium">${{ number_format($service->rate, 2) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface text-sm">
                                        @if($service->min_time && $service->max_time)
                                            @if($service->min_time === $service->max_time)
                                                ~{{ $service->min_time }}h
                                            @else
                                                {{ $service->min_time }}–{{ $service->max_time }}h
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $service->status === 'active' ? 'bg-tertiary' : 'bg-error' }}" 
                                              style="box-shadow:0 0 5px {{ $service->status === 'active' ? '#4edea3' : '#ffb4ab' }}"></span>
                                        <p class="text-on-surface text-sm capitalize font-medium">{{ $service->status }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('admin.services.toggle', $service->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-primary hover:text-primary/80 font-semibold text-sm transition-colors">
                                            {{ $service->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40">inventory_2</span>
                                        <p class="text-on-surface-variant text-sm">No services yet</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $services->links() }}
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Services')
@section('page-title', 'Services')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Services</h1>
                <p class="text-on-surface-variant mt-1">Manage all API provider services</p>
            </div>
        </div>

        {{-- Alerts --}}
        @if ($errors->has('error'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-error bg-error/5">
                <p class="text-error font-medium">{{ $errors->first('error') }}</p>
            </div>
        @endif

        @if (session('success'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5">
                <p class="text-tertiary font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Filters --}}
        <div class="glass-card p-4 rounded-xl mb-6">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span class="text-on-surface font-medium">Sort by:</span>
                        <div class="flex gap-2">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_direction' => request('sort_by') === 'name' && request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('sort_by') === 'name' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                                Name {{ request('sort_by') === 'name' ? (request('sort_direction') === 'asc' ? '↑' : '↓') : '' }}
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'price', 'sort_direction' => request('sort_by') === 'price' && request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('sort_by') === 'price' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                                Price {{ request('sort_by') === 'price' ? (request('sort_direction') === 'asc' ? '↑' : '↓') : '' }}
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'delivery_time', 'sort_direction' => request('sort_by') === 'delivery_time' && request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('sort_by') === 'delivery_time' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                                Delivery Time {{ request('sort_by') === 'delivery_time' ? (request('sort_direction') === 'asc' ? '↑' : '↓') : '' }}
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-error text-on-error rounded-lg text-sm font-medium hover:bg-error/80 transition-colors">
                        Reset Filters
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-on-surface font-medium">Tier:</span>
                    <div class="flex gap-2">
                        <a href="{{ request()->fullUrlWithQuery(['tier' => '']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ !request('tier') || request('tier') === '' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                            All
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['tier' => 'economy']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('tier') === 'economy' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                            Economy
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['tier' => 'standard']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('tier') === 'standard' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                            Standard
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['tier' => 'premium']) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ request('tier') === 'premium' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container/80' }} transition-colors">
                            Premium
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Services Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container">
                            <th class="px-6 py-4 text-left">
                                @php
                                    $isActive = request('sort_by') === 'name';
                                    $direction = $isActive && request('sort_direction') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_direction' => $direction]) }}" class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest hover:text-on-surface transition-colors {{ $isActive ? 'text-primary' : '' }}">
                                    Service
                                    @if($isActive)
                                        <span class="material-symbols-outlined text-xs align-middle">{{ request('sort_direction') === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Provider</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                @php
                                    $isActive = request('sort_by') === 'price';
                                    $direction = $isActive && request('sort_direction') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'price', 'sort_direction' => $direction]) }}" class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest hover:text-on-surface transition-colors {{ $isActive ? 'text-primary' : '' }}">
                                    Price
                                    @if($isActive)
                                        <span class="material-symbols-outlined text-xs align-middle">{{ request('sort_direction') === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left">
                                @php
                                    $isActive = request('sort_by') === 'delivery_time';
                                    $direction = $isActive && request('sort_direction') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'delivery_time', 'sort_direction' => $direction]) }}" class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest hover:text-on-surface transition-colors {{ $isActive ? 'text-primary' : '' }}">
                                    Delivery Time
                                    @if($isActive)
                                        <span class="material-symbols-outlined text-xs align-middle">{{ request('sort_direction') === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Status</p>
                            </th>
                            <th class="px-6 py-4 text-right">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                            <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-medium">{{ $service->name }}</p>
                                    @if ($service->description)
                                        <p class="text-on-surface-variant text-sm mt-1">{{ Str::limit($service->description, 50) }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-gradient-primary flex items-center justify-center text-white font-bold text-xs">
                                            {{ strtoupper(substr($service->apiProvider->name, 0, 1)) }}
                                        </div>
                                        <p class="text-on-surface text-sm">{{ $service->apiProvider->name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-medium">${{ number_format($service->rate, 2) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface text-sm">
                                        @if($service->min_time && $service->max_time)
                                            @if($service->min_time === $service->max_time)
                                                ~{{ $service->min_time }}h
                                            @else
                                                {{ $service->min_time }}–{{ $service->max_time }}h
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $service->status === 'active' ? 'bg-tertiary' : 'bg-error' }}" 
                                              style="box-shadow:0 0 5px {{ $service->status === 'active' ? '#4edea3' : '#ffb4ab' }}"></span>
                                        <p class="text-on-surface text-sm capitalize font-medium">{{ $service->status }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('admin.services.toggle', $service->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-primary hover:text-primary/80 font-semibold text-sm transition-colors">
                                            {{ $service->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40">inventory_2</span>
                                        <p class="text-on-surface-variant text-sm">No services yet</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $services->links() }}
        </div>
    </div>
</div>
@endsection
