@extends('layouts.app')
@section('title', 'Add Payment Account')
@section('page-title', 'Add Payment Account')

@section('content')
<div class="max-w-2xl mx-auto p-6">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.fund_accounts.index') }}"
           class="text-outline hover:text-on-surface transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-2xl font-bold text-on-surface">New Payment Account</h1>
    </div>

    <div class="glass-card rounded-xl p-6">
        <form method="POST" action="{{ route('admin.fund_accounts.store') }}">
            @csrf

            <div class="space-y-5">

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-on-surface-variant mb-2">
                        Account Name <span class="text-error">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           placeholder="e.g. JazzCash – Main"
                           class="w-full glass-input py-3 px-4 bg-transparent rounded-lg border border-outline-variant/40 focus:border-primary transition-colors @error('name') border-error @enderror"
                           required maxlength="100">
                    @error('name') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- IBAN --}}
                <div>
                    <label class="block text-sm font-medium text-on-surface-variant mb-2">IBAN</label>
                    <input type="text" name="iban" value="{{ old('iban') }}"
                           placeholder="PK00AAAA0000000000000000"
                           class="w-full glass-input py-3 px-4 bg-transparent rounded-lg border border-outline-variant/40 focus:border-primary transition-colors @error('iban') border-error @enderror"
                           maxlength="50">
                    @error('iban') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Account number --}}
                <div>
                    <label class="block text-sm font-medium text-on-surface-variant mb-2">Account / Wallet Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number') }}"
                           placeholder="03XX-XXXXXXX"
                           class="w-full glass-input py-3 px-4 bg-transparent rounded-lg border border-outline-variant/40 focus:border-primary transition-colors @error('account_number') border-error @enderror"
                           maxlength="100">
                    @error('account_number') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-on-surface-variant mb-2">Notes (shown to users)</label>
                    <textarea name="notes" rows="3"
                              placeholder="e.g. Send to this number then submit the TxID below."
                              class="w-full glass-input py-3 px-4 bg-transparent rounded-lg border border-outline-variant/40 focus:border-primary transition-colors resize-none @error('notes') border-error @enderror"
                              maxlength="500">{{ old('notes') }}</textarea>
                    @error('notes') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Active toggle --}}
                <div class="flex items-center justify-between py-3 border-t border-outline-variant/20">
                    <div>
                        <p class="text-sm font-medium text-on-surface">Immediately Active</p>
                        <p class="text-xs text-outline mt-0.5">Users will see this account right away on the Add Funds page.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               class="sr-only peer" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-outline/30 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-5 peer-checked:bg-primary after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                    </label>
                </div>

            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t border-outline-variant/20">
                <button type="submit"
                        class="bg-gradient-primary text-white px-6 py-3 rounded-xl font-semibold text-sm hover:brightness-110 transition-all">
                    Create Account
                </button>
                <a href="{{ route('admin.fund_accounts.index') }}"
                   class="px-6 py-3 rounded-xl border border-outline-variant/40 text-on-surface-variant text-sm hover:bg-white/5 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
