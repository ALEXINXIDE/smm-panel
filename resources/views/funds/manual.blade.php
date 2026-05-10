@extends('layouts.app')
@section('title', 'Add Funds – Manual Payment')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <div class="glass-card rounded-xl p-6 mb-6">
        <p class="text-on-surface-variant text-sm mb-1">Current Balance</p>
        <p class="text-3xl font-bold text-on-surface">${{ number_format(auth()->user()->funds ?? 0, 2) }}</p>
    </div>

    @if(session('success'))
        <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5 text-tertiary">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-8">
        @foreach($accounts as $acc)
            <button type="button" id="acc-{{ $acc->id }}" 
                class="account-btn glass-card p-4 rounded-xl border border-outline-variant/30 text-left hover:border-primary transition-all"
                onclick="selectAccount({{ $acc->id }}, '{{ $acc->name }}', '{{ strtoupper($acc->type) }}', '{{ $acc->account_number }}')">
                <p class="font-bold text-on-surface" data-method="{{ $acc->type }}">{{ $acc->name }}</p>
                <p class="text-xs text-outline">{{ strtoupper($acc->type) }}</p>
            </button>
        @endforeach
    </div>

    <div id="submit-section" class="glass-card rounded-xl p-6 hidden">
        <h2 class="text-lg font-bold text-on-surface mb-4">Submit Details</h2>
        <form action="{{ route('funds.request.store') }}" method="POST">
            @csrf
            <input type="hidden" name="payment_account_id" id="selected-account-id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Amount Sent (PKR)</label>
                    <input type="number" name="amount" id="auto-amount" class="glass-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Transaction ID (TID)</label>
                    <input type="text" name="transaction_id" class="glass-input w-full" required placeholder="e.g. 0123456789">
                </div>
                <button type="submit" class="w-full bg-gradient-primary text-white py-3 rounded-lg font-bold">Submit Verification</button>
            </div>
        </form>
    </div>
</div>

<script>
function selectAccount(id, name, type, number) {
    document.querySelectorAll('.account-btn').forEach(b => b.classList.replace('border-primary', 'border-outline-variant/30'));
    document.getElementById('acc-' + id).classList.replace('border-outline-variant/30', 'border-primary');
    document.getElementById('selected-account-id').value = id;
    document.getElementById('submit-section').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const amount = params.get('amount');
    const method = params.get('method');

    if(amount) document.getElementById('auto-amount').value = amount;
    if(method) {
        const btn = document.querySelector(`[data-method="${method}"]`)?.closest('.account-btn');
        if(btn) btn.click();
    }
});
</script>
@endsection
