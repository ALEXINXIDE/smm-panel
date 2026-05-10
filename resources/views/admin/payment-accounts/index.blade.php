@extends('layouts.app')
@section('title', 'Payment Accounts')
@section('page-title', 'Payment Accounts')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Payment Accounts</h1>
                <p class="text-on-surface-variant mt-1">Manage accounts users send money to</p>
            </div>
            <a href="{{ route('admin.fund-requests.index') }}"
               class="btn-primary px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">payments</span> Fund Requests
            </a>
        </div>

        @if(session('success'))
        <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5">
            <p class="text-tertiary font-medium">{{ session('success') }}</p>
        </div>
        @endif
        @if($errors->any())
        <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-error bg-error/5">
            <p class="text-error font-medium">{{ $errors->first() }}</p>
        </div>
        @endif

        {{-- Add Account --}}
        <div class="glass-card p-6 rounded-xl mb-6">
            <h2 class="text-lg font-semibold text-on-surface mb-4">Add New Account</h2>
            <form action="{{ route('admin.payment-accounts.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-on-surface mb-1">Display Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="glass-input w-full"
                               placeholder="e.g. EasyPaisa – Ali Khan" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-on-surface mb-1">Type</label>
                        <select name="type" class="glass-input w-full" required>
                            <option value="">Select type</option>
                            <option value="easypaisa" {{ old('type')=='easypaisa'?'selected':'' }}>EasyPaisa</option>
                            <option value="jazzcash"  {{ old('type')=='jazzcash'?'selected':'' }}>JazzCash</option>
                            <option value="bank"      {{ old('type')=='bank'?'selected':'' }}>Bank Transfer</option>
                            <option value="crypto"    {{ old('type')=='crypto'?'selected':'' }}>Crypto</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-on-surface mb-1">Account Number / IBAN / Address</label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}" class="glass-input w-full"
                               placeholder="03001234567 or IBAN" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-on-surface mb-1">Account Title</label>
                        <input type="text" name="account_title" value="{{ old('account_title') }}" class="glass-input w-full"
                               placeholder="Account holder name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-on-surface mb-1">
                            Bank Name <span class="text-outline text-xs">(for bank transfers)</span>
                        </label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="glass-input w-full"
                               placeholder="e.g. HBL, Meezan Bank">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn-primary px-6 py-2 rounded-lg font-semibold">Add Account</button>
                </div>
            </form>
        </div>

        {{-- Accounts Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/30 bg-surface-container">
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Number / IBAN</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Requests</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                    <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="text-on-surface font-semibold">{{ $account->name }}</p>
                            @if($account->account_title)
                                <p class="text-on-surface-variant text-xs">{{ $account->account_title }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-primary/20 text-primary">
                                {{ $account->typeLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-on-surface font-mono text-sm">{{ $account->account_number }}</p>
                            @if($account->bank_name)
                                <p class="text-on-surface-variant text-xs">{{ $account->bank_name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-on-surface font-semibold">{{ $account->fund_requests_count }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $account->is_active ? 'bg-tertiary/20 text-tertiary' : 'bg-error/20 text-error' }}">
                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <form action="{{ route('admin.payment-accounts.toggle', $account) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-sm font-semibold transition-opacity hover:opacity-70
                                                   {{ $account->is_active ? 'text-error' : 'text-tertiary' }}">
                                        {{ $account->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.payment-accounts.destroy', $account) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this account? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-semibold text-error hover:opacity-70 transition-opacity">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <p class="text-on-surface-variant text-sm">No payment accounts yet. Add one above.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
