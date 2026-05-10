@extends('layouts.app')
@section('title', 'Transactions')
@section('page-title', 'Transactions')

@section('content')
<div class="glass-card rounded-xl overflow-hidden fade-up">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="border-b border-outline-variant/30">
                    <th class="px-5 py-3 text-left font-label-caps text-label-caps text-outline font-normal">ID</th>
                    <th class="px-5 py-3 text-left font-label-caps text-label-caps text-outline font-normal">Type</th>
                    <th class="px-5 py-3 text-left font-label-caps text-label-caps text-outline font-normal">Description</th>
                    <th class="px-5 py-3 text-right font-label-caps text-label-caps text-outline font-normal">Amount</th>
                    <th class="px-5 py-3 text-center font-label-caps text-label-caps text-outline font-normal">Status</th>
                    <th class="px-5 py-3 text-right font-label-caps text-label-caps text-outline font-normal hidden md:table-cell">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                @php
                    $isCredit = in_array($tx->type,['deposit','referral_bonus','refund']);
                    $sc = $tx->status === 'completed'
                        ? 'bg-tertiary/10 text-tertiary border-tertiary/30'
                        : ($tx->status === 'pending'
                            ? 'bg-[#fcd34d]/10 text-[#fcd34d] border-[#fcd34d]/30'
                            : 'bg-error/10 text-error border-error/30');
                @endphp
                <tr class="border-b border-surface-container-high hover:bg-white/5 transition-colors">
                    <td class="px-5 py-4 font-mono text-outline text-xs">#{{ $tx->id }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px] {{ $isCredit ? 'text-tertiary' : 'text-error' }}">
                                {{ $isCredit ? 'add_circle' : 'remove_circle' }}
                            </span>
                            <span class="text-on-surface-variant text-sm capitalize">{{ str_replace('_',' ', $tx->type) }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-outline text-xs max-w-[180px] truncate">{{ $tx->description ?? '—' }}</td>
                    <td class="px-5 py-4 text-right font-bold {{ $isCredit ? 'text-tertiary' : 'text-error' }}">
                        {{ $isCredit ? '+' : '-' }}${{ number_format($tx->amount, 4) }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider {{ $sc }}">
                            {{ ucfirst($tx->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right text-outline text-xs hidden md:table-cell">{{ $tx->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center text-outline">
                        <span class="material-symbols-outlined text-[48px] block mb-3 opacity-20">receipt_long</span>
                        No transactions yet
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="px-5 py-4 border-t border-outline-variant/30 flex items-center justify-between">
        <p class="text-xs text-outline">{{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }}</p>
        <div class="flex gap-2">
            @if(!$transactions->onFirstPage())
            <a href="{{ $transactions->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-on-surface border border-outline-variant/30 rounded-lg hover:bg-white/5">← Prev</a>
            @endif
            @if($transactions->hasMorePages())
            <a href="{{ $transactions->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-on-surface border border-outline-variant/30 rounded-lg hover:bg-white/5">Next →</a>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
