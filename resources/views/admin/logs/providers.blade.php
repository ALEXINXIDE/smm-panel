@extends('layouts.app')

@section('title', 'Provider Logs')
@section('page-title', 'Provider Logs')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Provider API Logs</h1>
                <p class="text-on-surface-variant mt-1">Track API provider integration requests</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="glass-card p-4 rounded-xl mb-6 flex flex-col sm:flex-row gap-4">
            <form action="{{ route('admin.logs.providers') }}" method="GET" class="flex gap-4 flex-1">
                <input type="text" name="provider_id" placeholder="Filter by provider ID..." 
                       value="{{ request('provider_id') }}" class="glass-input flex-1">
                
                <select name="status" class="glass-input" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="timeout" {{ request('status') === 'timeout' ? 'selected' : '' }}>Timeout</option>
                </select>

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
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Provider</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Action</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Status</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Response Time</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Details</p>
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
                                    <div class="w-8 h-8 rounded-lg bg-gradient-primary flex items-center justify-center text-white font-bold text-xs">
                                        {{ strtoupper(substr('P', 0, 1)) }}
                                    </div>
                                    <p class="text-on-surface font-medium text-sm">Provider #{{ $log->api_provider_id }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-secondary/20 text-secondary uppercase">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'success' => 'bg-tertiary/20 text-tertiary',
                                            'failed' => 'bg-error/20 text-error',
                                            'timeout' => 'bg-yellow/20 text-yellow',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$log->status] ?? 'bg-outline/20 text-outline' }} capitalize">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface text-sm font-mono">{{ $log->response_time ?? 'N/A' }}ms</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm truncate">{{ Str::limit($log->response_body ?? 'No response', 40) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40 block mb-2">api</span>
                                    <p class="text-on-surface-variant text-sm">No provider logs found</p>
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

@section('title', 'Provider Logs')
@section('page-title', 'Provider Logs')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Provider API Logs</h1>
                <p class="text-on-surface-variant mt-1">Track API provider integration requests</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="glass-card p-4 rounded-xl mb-6 flex flex-col sm:flex-row gap-4">
            <form action="{{ route('admin.logs.providers') }}" method="GET" class="flex gap-4 flex-1">
                <input type="text" name="provider_id" placeholder="Filter by provider ID..." 
                       value="{{ request('provider_id') }}" class="glass-input flex-1">
                
                <select name="status" class="glass-input" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="timeout" {{ request('status') === 'timeout' ? 'selected' : '' }}>Timeout</option>
                </select>

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
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Provider</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Action</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Status</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Response Time</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Details</p>
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
                                    <div class="w-8 h-8 rounded-lg bg-gradient-primary flex items-center justify-center text-white font-bold text-xs">
                                        {{ strtoupper(substr('P', 0, 1)) }}
                                    </div>
                                    <p class="text-on-surface font-medium text-sm">Provider #{{ $log->api_provider_id }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-secondary/20 text-secondary uppercase">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'success' => 'bg-tertiary/20 text-tertiary',
                                            'failed' => 'bg-error/20 text-error',
                                            'timeout' => 'bg-yellow/20 text-yellow',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$log->status] ?? 'bg-outline/20 text-outline' }} capitalize">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface text-sm font-mono">{{ $log->response_time ?? 'N/A' }}ms</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm truncate">{{ Str::limit($log->response_body ?? 'No response', 40) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40 block mb-2">api</span>
                                    <p class="text-on-surface-variant text-sm">No provider logs found</p>
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
