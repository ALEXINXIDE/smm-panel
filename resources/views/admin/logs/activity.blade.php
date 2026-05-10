@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Activity Logs</h1>
                <p class="text-on-surface-variant mt-1">Track all platform user activities</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="glass-card p-4 rounded-xl mb-6 flex flex-col sm:flex-row gap-4">
            <form action="{{ route('admin.logs.activity') }}" method="GET" class="flex gap-4 flex-1">
                <input type="text" name="user_id" placeholder="Filter by user ID..." 
                       value="{{ request('user_id') }}" class="glass-input flex-1">
                
                <input type="text" name="action" placeholder="Filter by action..." 
                       value="{{ request('action') }}" class="glass-input flex-1">

                <button type="submit" class="btn-primary px-6 rounded-lg">Filter</button>
            </form>
        </div>

        {{-- Logs Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container">
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">User</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Action</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Description</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Timestamp</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-primary flex items-center justify-center text-white font-bold text-xs">
                                            {{ strtoupper(substr($log->user?->name ?? 'N', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-on-surface font-medium">{{ $log->user?->name ?? 'Unknown' }}</p>
                                            <p class="text-on-surface-variant text-xs">ID: {{ $log->user_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-primary/20 text-primary uppercase">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface text-sm">{{ Str::limit($log->description, 60) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ $log->created_at->format('d M Y H:i') }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40 block mb-2">history</span>
                                    <p class="text-on-surface-variant text-sm">No activity logs found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Activity Logs</h1>
                <p class="text-on-surface-variant mt-1">Track all platform user activities</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="glass-card p-4 rounded-xl mb-6 flex flex-col sm:flex-row gap-4">
            <form action="{{ route('admin.logs.activity') }}" method="GET" class="flex gap-4 flex-1">
                <input type="text" name="user_id" placeholder="Filter by user ID..." 
                       value="{{ request('user_id') }}" class="glass-input flex-1">
                
                <input type="text" name="action" placeholder="Filter by action..." 
                       value="{{ request('action') }}" class="glass-input flex-1">

                <button type="submit" class="btn-primary px-6 rounded-lg">Filter</button>
            </form>
        </div>

        {{-- Logs Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container">
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">User</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Action</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Description</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Timestamp</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-primary flex items-center justify-center text-white font-bold text-xs">
                                            {{ strtoupper(substr($log->user?->name ?? 'N', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-on-surface font-medium">{{ $log->user?->name ?? 'Unknown' }}</p>
                                            <p class="text-on-surface-variant text-xs">ID: {{ $log->user_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-primary/20 text-primary uppercase">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface text-sm">{{ Str::limit($log->description, 60) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ $log->created_at->format('d M Y H:i') }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40 block mb-2">history</span>
                                    <p class="text-on-surface-variant text-sm">No activity logs found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
