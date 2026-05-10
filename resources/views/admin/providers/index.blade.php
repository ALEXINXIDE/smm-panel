@extends('layouts.app')

@section('title', 'API Providers')
@section('page-title', 'API Providers')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">API Providers</h1>
                <p class="text-on-surface-variant mt-1">Manage API provider integrations</p>
            </div>
            <a href="{{ route('admin.providers.create') }}" class="btn-primary px-6 py-3 rounded-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Add Provider
            </a>
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

        {{-- Providers Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($providers as $provider)
                <div class="glass-card rounded-xl p-6 border border-outline-variant/20 hover:border-outline-variant/50 transition-all">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-primary flex items-center justify-center text-white font-bold text-lg">
                                {{ strtoupper(substr($provider->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-on-surface font-semibold">{{ $provider->name }}</p>
                                <p class="text-on-surface-variant text-xs">{{ $provider->services_count }} services</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $provider->status === 'active' ? 'bg-tertiary/20 text-tertiary' : 'bg-error/20 text-error' }}">
                            {{ ucfirst($provider->status) }}
                        </span>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div>
                            <p class="text-on-surface-variant text-xs font-label-caps uppercase tracking-widest mb-1">URL</p>
                            <p class="text-on-surface text-sm truncate" title="{{ $provider->url }}">{{ $provider->url }}</p>
                        </div>
                        <div>
                            <p class="text-on-surface-variant text-xs font-label-caps uppercase tracking-widest mb-1">Price Markup</p>
                            <p class="text-on-surface text-sm font-semibold">{{ $provider->percentage_increase }}%</p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <form action="{{ route('admin.providers.sync', $provider->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full text-primary hover:text-primary/80 font-semibold text-sm py-2 rounded-lg border border-primary/30 hover:border-primary/50 transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">sync</span>
                                Sync Services
                            </button>
                        </form>
                        <a href="{{ route('admin.providers.edit', $provider->id) }}" class="flex-1 text-outline hover:text-on-surface font-semibold text-sm py-2 rounded-lg border border-outline-variant/50 hover:border-outline-variant transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">edit</span>
                            Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="lg:col-span-3 glass-card p-12 rounded-xl text-center">
                    <span class="material-symbols-outlined text-[48px] text-outline-variant opacity-30 block mb-4">cloud_off</span>
                    <p class="text-on-surface-variant text-sm mb-4">No API providers configured yet</p>
                    <a href="{{ route('admin.providers.create') }}" class="btn-primary px-6 py-3 rounded-lg inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        Create First Provider
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'API Providers')
@section('page-title', 'API Providers')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">API Providers</h1>
                <p class="text-on-surface-variant mt-1">Manage API provider integrations</p>
            </div>
            <a href="{{ route('admin.providers.create') }}" class="btn-primary px-6 py-3 rounded-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Add Provider
            </a>
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

        {{-- Providers Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($providers as $provider)
                <div class="glass-card rounded-xl p-6 border border-outline-variant/20 hover:border-outline-variant/50 transition-all">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-primary flex items-center justify-center text-white font-bold text-lg">
                                {{ strtoupper(substr($provider->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-on-surface font-semibold">{{ $provider->name }}</p>
                                <p class="text-on-surface-variant text-xs">{{ $provider->services_count }} services</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $provider->status === 'active' ? 'bg-tertiary/20 text-tertiary' : 'bg-error/20 text-error' }}">
                            {{ ucfirst($provider->status) }}
                        </span>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div>
                            <p class="text-on-surface-variant text-xs font-label-caps uppercase tracking-widest mb-1">URL</p>
                            <p class="text-on-surface text-sm truncate" title="{{ $provider->url }}">{{ $provider->url }}</p>
                        </div>
                        <div>
                            <p class="text-on-surface-variant text-xs font-label-caps uppercase tracking-widest mb-1">Price Markup</p>
                            <p class="text-on-surface text-sm font-semibold">{{ $provider->percentage_increase }}%</p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <form action="{{ route('admin.providers.sync', $provider->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full text-primary hover:text-primary/80 font-semibold text-sm py-2 rounded-lg border border-primary/30 hover:border-primary/50 transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">sync</span>
                                Sync Services
                            </button>
                        </form>
                        <a href="{{ route('admin.providers.edit', $provider->id) }}" class="flex-1 text-outline hover:text-on-surface font-semibold text-sm py-2 rounded-lg border border-outline-variant/50 hover:border-outline-variant transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">edit</span>
                            Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="lg:col-span-3 glass-card p-12 rounded-xl text-center">
                    <span class="material-symbols-outlined text-[48px] text-outline-variant opacity-30 block mb-4">cloud_off</span>
                    <p class="text-on-surface-variant text-sm mb-4">No API providers configured yet</p>
                    <a href="{{ route('admin.providers.create') }}" class="btn-primary px-6 py-3 rounded-lg inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        Create First Provider
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
