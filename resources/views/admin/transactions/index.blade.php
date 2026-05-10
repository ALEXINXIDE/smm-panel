@extends('layouts.app')

@section('title', 'Transactions')
@section('page-title', 'Transactions')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Transactions</h1>
                <p class="text-on-surface-variant mt-1">Track all payment transactions</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="glass-card p-4 rounded-xl mb-6 flex flex-col sm:flex-row gap-4">
            <form action="{{ route('admin.transactions.index') }}" method="GET" class="flex gap-4 flex-1">
                <select name="status" class="glass-input" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>

                <select name="type" class="glass-input" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                    <option value="order" {{ request('type') === 'order' ? 'selected' : '' }}>Order</option>
                </select>
            </form>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5">
                <p class="text-tertiary font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-error bg-error/5">
                <p class="text-error font-medium">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Transactions Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container">
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">ID</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">User</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Amount</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Type</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Status</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Date</p>
                            </th>
                            <th class="px-6 py-4 text-right">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-semibold">#{{ $tx->id }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-medium">{{ $tx->user->name }}</p>
                                    <p class="text-on-surface-variant text-xs">{{ $tx->user->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-semibold">{{ $tx->amount > 0 ? '+' : '' }}PKR {{ number_format(abs($tx->amount), 2) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-primary/20 text-primary capitalize">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow/20 text-yellow',
                                            'completed' => 'bg-tertiary/20 text-tertiary',
                                            'failed' => 'bg-error/20 text-error',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$tx->status] ?? 'bg-outline/20 text-outline' }} capitalize">
                                        {{ $tx->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ $tx->created_at->format('d M Y H:i') }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($tx->status === 'pending')
                                        <form method="POST" class="inline">
                                            @csrf
                                            <div class="flex gap-2">
                                                <form action="{{ route('admin.transactions.approve', $tx->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-tertiary hover:text-tertiary/80 font-semibold text-sm transition-colors">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.transactions.reject', $tx->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-error hover:text-error/80 font-semibold text-sm transition-colors">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </form>
                                    @else
                                        <span class="text-on-surface-variant text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40 block mb-2">payment</span>
                                    <p class="text-on-surface-variant text-sm">No transactions found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Transactions')
@section('page-title', 'Transactions')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Transactions</h1>
                <p class="text-on-surface-variant mt-1">Track all payment transactions</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="glass-card p-4 rounded-xl mb-6 flex flex-col sm:flex-row gap-4">
            <form action="{{ route('admin.transactions.index') }}" method="GET" class="flex gap-4 flex-1">
                <select name="status" class="glass-input" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>

                <select name="type" class="glass-input" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                    <option value="order" {{ request('type') === 'order' ? 'selected' : '' }}>Order</option>
                </select>
            </form>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5">
                <p class="text-tertiary font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-error bg-error/5">
                <p class="text-error font-medium">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Transactions Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container">
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">ID</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">User</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Amount</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Type</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Reference / Account</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Status</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Date</p>
                            </th>
                            <th class="px-6 py-4 text-right">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-semibold">#{{ $tx->id }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-medium">{{ $tx->user->name }}</p>
                                    <p class="text-on-surface-variant text-xs">{{ $tx->user->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-semibold">{{ $tx->amount > 0 ? '+' : '' }}PKR {{ number_format(abs($tx->amount), 2) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-primary/20 text-primary capitalize">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-semibold">{{ $tx->reference ?? '—' }}</p>
                                    <p class="text-on-surface-variant text-xs mt-1">{{ $tx->fundAccount?->name ?? strtoupper($tx->gateway ?? 'manual') }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow/20 text-yellow',
                                            'completed' => 'bg-tertiary/20 text-tertiary',
                                            'failed' => 'bg-error/20 text-error',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$tx->status] ?? 'bg-outline/20 text-outline' }} capitalize">
                                        {{ $tx->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ $tx->created_at->format('d M Y H:i') }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($tx->status === 'pending')
                                        <div class="flex gap-2 justify-end">
                                            <form action="{{ route('admin.transactions.approve', $tx->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-tertiary hover:text-tertiary/80 font-semibold text-sm transition-colors">
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.transactions.reject', $tx->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-error hover:text-error/80 font-semibold text-sm transition-colors">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-on-surface-variant text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40 block mb-2">payment</span>
                                    <p class="text-on-surface-variant text-sm">No transactions found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
