@extends('layouts.app')
@section('title', 'Add Funds')
@section('page-title', 'Add Funds')

@section('css')
<style>
.method-card {
    background: rgba(23,31,51,0.5);
    border: 1px solid rgba(173,198,255,0.1);
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s;
}
.method-card:hover  { border-color: #adc6ff; box-shadow: 0 0 15px rgba(173,198,255,0.1); }
.method-card.selected { border-color: #adc6ff; background: rgba(173,198,255,0.07); box-shadow: 0 0 15px rgba(173,198,255,0.2); }
.method-card .check { opacity: 0; transition: opacity 0.2s; }
.method-card.selected .check { opacity: 1; }
.quick-btn { padding: 8px 16px; border-radius: 8px; border: 1px solid rgba(173,198,255,0.2); font-size: 13px; font-weight: 600; color: #8c909f; background: transparent; cursor: pointer; transition: all 0.15s; }
.quick-btn:hover, .quick-btn.active { border-color: #adc6ff; background: rgba(173,198,255,0.08); color: #adc6ff; }
</style>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">

{{-- Balance card --}}
<div class="glass-card rounded-xl p-md mb-6 relative overflow-hidden fade-up">
    <div class="absolute inset-0 opacity-10" style="background:linear-gradient(135deg,#4d8eff22,transparent)"></div>
    <div class="relative z-10 flex items-center justify-between">
        <div>
            <p class="font-label-caps text-label-caps text-outline mb-2">Current Balance</p>
            <div class="flex items-baseline gap-6">
                <div>
                    <p class="font-h1 text-on-surface neon-text-primary" style="font-size:40px">${{ number_format(auth()->user()->funds ?? 0, 2) }}</p>
                    <p class="text-xs text-outline mt-1">USD</p>
                </div>
                <div class="border-l border-outline-variant/30 pl-6">
                    <p class="font-h2 text-tertiary" style="font-size:28px">₨{{ number_format((auth()->user()->funds ?? 0) * session('usd_pkr_rate',280), 0) }}</p>
                    <p class="text-xs text-outline mt-1">PKR @ {{ session('usd_pkr_rate',280) }}</p>
                </div>
            </div>
        </div>
        <div class="w-14 h-14 rounded-xl bg-gradient-primary flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-white text-[28px]">account_balance_wallet</span>
        </div>
    </div>
</div>

@if($accounts->isEmpty())
{{-- No active payment methods --}}
<div class="glass-card rounded-xl p-md text-center fade-up">
    <span class="material-symbols-outlined text-[48px] text-outline opacity-30 block mb-3">payment</span>
    <p class="text-on-surface font-semibold mb-2">No Payment Methods Available</p>
    <p class="text-outline text-sm mb-4">Our admin team is setting up payment accounts. Please check back soon or contact support.</p>
    @if($whatsappLink)
    <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
       class="inline-flex items-center gap-2 bg-gradient-primary text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:brightness-110 transition-all">
        <span class="material-symbols-outlined text-[18px]">support_agent</span> Contact Support
    </a>
    @endif
</div>
@else

{{-- Payment method selector (from DB) --}}
<div class="mb-6 fade-up">
    <h2 class="font-h3 text-h3 text-primary flex items-center gap-2 mb-4">
        <span class="material-symbols-outlined">credit_card</span> Select Payment Method
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="method-grid">
        @foreach($accounts as $account)
        <button type="button"
            class="method-card text-left"
            onclick="selectAccount({{ $account->id }})"
            data-id="{{ $account->id }}">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-[24px]">account_balance</span>
                    <div>
                        <p class="text-on-surface font-semibold text-sm">{{ $account->name }}</p>
                        @if($account->iban)
                        <p class="text-outline text-xs">IBAN: {{ $account->iban }}</p>
                        @elseif($account->account_number)
                        <p class="text-outline text-xs">Acc: {{ $account->account_number }}</p>
                        @endif
                    </div>
                </div>
                <div class="check w-5 h-5 rounded-full bg-gradient-primary flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-white text-[12px]">check</span>
                </div>
            </div>
            @if($account->notes)
            <p class="text-xs text-outline border-t border-outline-variant/20 pt-2 mt-1">{{ Str::limit($account->notes, 80) }}</p>
            @endif
        </button>
        @endforeach
    </div>
</div>

{{-- Deposit form --}}
<div id="payment-form-wrapper" class="fade-up" style="display:none">
    <div class="glass-card rounded-xl p-md mb-6">
        <h3 class="font-h3 text-h3 text-on-surface mb-5">Deposit Details</h3>

        @if($errors->any())
        <div class="bg-error/10 border border-error/30 rounded-lg p-4 mb-5">
            <ul class="text-error text-sm space-y-1 list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('funds.manual') }}" enctype="multipart/form-data">
            @csrf

            {{-- Hidden selected account --}}
            <input type="hidden" name="fund_account_id" id="selected-account-id">

            {{-- Amount --}}
            <div class="mb-5">
                <label class="block text-sm text-on-surface-variant mb-2 font-medium">
                    Amount (PKR) <span class="text-error">*</span>
                </label>
                {{-- Quick amounts --}}
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach([500, 1000, 2000, 5000, 10000] as $amt)
                    <button type="button"
                        class="quick-btn"
                        onclick="setAmount({{ $amt }})">
                        ₨{{ number_format($amt) }}
                    </button>
                    @endforeach
                </div>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-outline font-bold">₨</span>
                    <input type="number" name="amount" id="amount-input"
                           value="{{ old('amount') }}"
                           placeholder="0"
                           min="{{ config('services.payments.min_deposit', 100) }}"
                           max="{{ config('services.payments.max_deposit', 500000) }}"
                           step="1"
                           class="w-full glass-input py-3 pl-8 pr-4 text-xl font-bold bg-transparent rounded-lg border border-outline-variant/40 focus:border-primary transition-colors @error('amount') border-error @enderror"
                           required>
                </div>
                @error('amount')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-outline mt-1" id="usd-preview"></p>
            </div>

            {{-- Transaction reference --}}
            <div class="mb-5">
                <label class="block text-sm text-on-surface-variant mb-2 font-medium">
                    Transaction ID / Reference <span class="text-error">*</span>
                </label>
                <input type="text" name="reference"
                       value="{{ old('reference') }}"
                       placeholder="e.g. TXN-12345ABCDE"
                       class="w-full glass-input py-3 px-4 bg-transparent rounded-lg border border-outline-variant/40 focus:border-primary transition-colors text-sm @error('reference') border-error @enderror"
                       required maxlength="100">
                @error('reference')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-outline mt-1">Enter the transaction ID from your payment app.</p>
            </div>

            {{-- Screenshot (optional) --}}
            <div class="mb-6">
                <label class="block text-sm text-on-surface-variant mb-2 font-medium">
                    Payment Screenshot <span class="text-outline text-xs">(optional but helps speed up approval)</span>
                </label>
                <input type="file" name="screenshot" accept="image/*"
                       class="w-full text-sm text-outline file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-colors @error('screenshot') border-error @enderror">
                @error('screenshot')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-gradient-primary text-white py-3.5 px-6 rounded-xl font-semibold text-sm hover:brightness-110 transition-all neon-glow-primary">
                <span class="material-symbols-outlined align-middle mr-1 text-[18px]">send</span>
                Submit Payment Request
            </button>

            <p class="text-xs text-outline text-center mt-4">
                Requests are reviewed within 1–24 hours. Your balance will be credited after verification.
            </p>
        </form>
    </div>
</div>

@if($whatsappLink)
<div class="text-center mb-6 fade-up">
    <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
       class="inline-flex items-center gap-2 text-outline hover:text-on-surface text-sm transition-colors">
        <span class="material-symbols-outlined text-[16px]">support_agent</span>
        Need help? Contact support on WhatsApp
    </a>
</div>
@endif

@endif {{-- end $accounts->isEmpty() --}}

</div>
@endsection

@section('scripts')
<script>
const rate = {{ session('usd_pkr_rate', 280) }};

function selectAccount(id) {
    // Highlight selected card
    document.querySelectorAll('.method-card').forEach(c => {
        c.classList.toggle('selected', c.dataset.id == id);
    });
    // Set hidden field
    document.getElementById('selected-account-id').value = id;
    // Show payment form
    document.getElementById('payment-form-wrapper').style.display = '';
    document.getElementById('payment-form-wrapper').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function setAmount(pkr) {
    document.getElementById('amount-input').value = pkr;
    updatePreview(pkr);
    document.querySelectorAll('.quick-btn').forEach(b =>
        b.classList.toggle('active', parseInt(b.textContent.replace(/[^0-9]/g,'')) === pkr)
    );
}

function updatePreview(pkr) {
    const usd = (pkr / rate).toFixed(2);
    document.getElementById('usd-preview').textContent =
        pkr > 0 ? `≈ $${usd} USD at today's rate (₨${rate}/USD)` : '';
}

document.getElementById('amount-input')?.addEventListener('input', e => {
    updatePreview(parseFloat(e.target.value) || 0);
    document.querySelectorAll('.quick-btn').forEach(b => b.classList.remove('active'));
});

// Re-open form if there were validation errors (old input exists)
@if(old('fund_account_id'))
selectAccount({{ old('fund_account_id') }});
@endif
</script>
@endsection
