@extends('layouts.app')

@section('title', 'Payment Logs')
@section('page-title', 'Payment Logs')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Payment Logs</h1>
                <p class="text-on-surface-variant mt-1">Track all payment gateway transactions</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="glass-card p-4 rounded-xl mb-6 flex flex-col sm:flex-row gap-4">
            <form action="{{ route('admin.logs.payments') }}" method="GET" class="flex gap-4 flex-1">
                <input type="text" name="user_id" placeholder="Filter by user ID..." 
                       value="{{ request('user_id') }}" class="glass-input flex-1">
                
                <select name="status" class="glass-input" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
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
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Transaction ID</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">User ID</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Amount</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Method</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Status</p>
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
                                    <p class="text-on-surface font-semibold font-mono text-sm">{{ Str::limit($log->transaction_id, 20) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface text-sm">#{{ $log->user_id }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-semibold">PKR {{ number_format($log->amount, 2) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-secondary/20 text-secondary capitalize">
                                        {{ $log->payment_method }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'success' => 'bg-tertiary/20 text-tertiary',
                                            'failed' => 'bg-error/20 text-error',
                                            'pending' => 'bg-yellow/20 text-yellow',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$log->status] ?? 'bg-outline/20 text-outline' }} capitalize">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40 block mb-2">receipt</span>
                                    <p class="text-on-surface-variant text-sm">No payment logs found</p>
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

@section('title', 'Payment Logs')
@section('page-title', 'Payment Logs')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Payment Logs</h1>
                <p class="text-on-surface-variant mt-1">Track all payment gateway transactions</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="glass-card p-4 rounded-xl mb-6 flex flex-col sm:flex-row gap-4">
            <form action="{{ route('admin.logs.payments') }}" method="GET" class="flex gap-4 flex-1">
                <input type="text" name="user_id" placeholder="Filter by user ID..." 
                       value="{{ request('user_id') }}" class="glass-input flex-1">
                
                <select name="status" class="glass-input" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
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
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Transaction ID</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">User ID</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Amount</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Method</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Status</p>
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
                                    <p class="text-on-surface font-semibold font-mono text-sm">{{ Str::limit($log->transaction_id, 20) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface text-sm">#{{ $log->user_id }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-semibold">PKR {{ number_format($log->amount, 2) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-secondary/20 text-secondary capitalize">
                                        {{ $log->payment_method }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'success' => 'bg-tertiary/20 text-tertiary',
                                            'failed' => 'bg-error/20 text-error',
                                            'pending' => 'bg-yellow/20 text-yellow',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$log->status] ?? 'bg-outline/20 text-outline' }} capitalize">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40 block mb-2">receipt</span>
                                    <p class="text-on-surface-variant text-sm">No payment logs found</p>
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
