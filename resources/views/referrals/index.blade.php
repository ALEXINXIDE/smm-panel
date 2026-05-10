@extends('layouts.app')
@section('title', 'Referral Program')
@section('page-title', 'Referral Program')

@section('content')
<div class="max-w-3xl mx-auto">

{{-- Hero --}}
<div class="glass-card rounded-xl p-md mb-6 relative overflow-hidden fade-up">
    <div class="absolute inset-0 opacity-10" style="background:linear-gradient(135deg,#571bc144,#4edea322)"></div>
    <div class="relative z-10 text-center">
        <h2 class="font-h1 text-on-surface mb-2" style="font-size:36px">Referral Program</h2>
        <p class="text-on-surface-variant text-sm max-w-md mx-auto">Invite friends and earn <span class="text-tertiary font-bold">5% commission</span> on every deposit they make — credited instantly to your wallet.</p>
    </div>
</div>

{{-- Stats row --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 fade-up">
    <div class="glass-card rounded-xl p-sm text-center">
        <p class="font-label-caps text-label-caps text-outline mb-2">Total Referrals</p>
        <p class="text-h2 text-on-surface neon-text-primary font-bold" style="font-size:32px">{{ $stats['total_referrals'] }}</p>
    </div>
    <div class="glass-card rounded-xl p-sm text-center">
        <p class="font-label-caps text-label-caps text-outline mb-2">Total Earned</p>
        <p class="text-h2 text-tertiary font-bold" style="font-size:32px">${{ number_format($stats['total_earned'], 2) }}</p>
        <p class="text-xs text-outline">₨{{ number_format($stats['total_earned'] * session('usd_pkr_rate',280), 0) }}</p>
    </div>
    <div class="glass-card rounded-xl p-sm text-center">
        <p class="font-label-caps text-label-caps text-outline mb-2">This Month</p>
        <p class="text-h2 text-secondary font-bold" style="font-size:32px">${{ number_format($stats['earned_month'], 2) }}</p>
    </div>
</div>

{{-- Referral link --}}
<div class="glass-card rounded-xl p-md mb-6 fade-up">
    <h3 class="font-h2 text-h2 text-on-surface mb-4" style="font-size:22px">Your Unique Link</h3>
    <div class="flex items-center gap-3 bg-surface-container-low rounded-xl p-3 border border-outline-variant/40 mb-3">
        <span class="material-symbols-outlined text-primary flex-shrink-0">link</span>
        <input type="text" id="ref-link" value="{{ url('/register?ref=' . auth()->user()->referral_code) }}"
            class="flex-1 bg-transparent text-on-surface text-sm font-mono border-none outline-none" readonly>
        <button onclick="copyLink()" class="flex-shrink-0 bg-gradient-primary text-white px-4 py-2 rounded-lg text-xs font-semibold hover:brightness-110 transition-all flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px]">content_copy</span> <span id="copy-label">Copy</span>
        </button>
    </div>
    <div class="flex gap-3">
        <a href="https://wa.me/?text={{ urlencode('Join SMM Elite and get great SMM services! Use my referral link: ' . url('/register?ref=' . auth()->user()->referral_code)) }}"
            target="_blank"
            class="flex items-center gap-2 px-4 py-2 rounded-lg border border-outline-variant/40 text-outline hover:text-on-surface hover:bg-white/5 transition-all text-sm">
            <span class="material-symbols-outlined text-[16px]">share</span> WhatsApp
        </a>
        <a href="https://t.me/share/url?url={{ urlencode(url('/register?ref=' . auth()->user()->referral_code)) }}&text={{ urlencode('Join SMM Elite — great prices on followers, views & more!') }}"
            target="_blank"
            class="flex items-center gap-2 px-4 py-2 rounded-lg border border-outline-variant/40 text-outline hover:text-on-surface hover:bg-white/5 transition-all text-sm">
            <span class="material-symbols-outlined text-[16px]">send</span> Telegram
        </a>
    </div>
</div>

{{-- Referral list --}}
<div class="glass-card rounded-xl p-md fade-up">
    <h3 class="font-h3 text-h3 text-on-surface mb-5">Your Referrals</h3>
    @forelse($referrals as $ref)
    <div class="flex items-center justify-between py-3 border-b border-outline-variant/20">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-gradient-primary flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr($ref->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-on-surface text-sm font-medium">{{ $ref->name }}</p>
                <p class="text-outline text-xs">Joined {{ $ref->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-tertiary font-semibold text-sm">${{ number_format($ref->referral_commission ?? 0, 2) }} earned</p>
            <p class="text-outline text-xs">{{ $ref->orders_count ?? 0 }} orders</p>
        </div>
    </div>
    @empty
    <div class="text-center py-12 text-outline">
        <span class="material-symbols-outlined text-[48px] block mb-3 opacity-20">group_add</span>
        <p class="mb-1">No referrals yet</p>
        <p class="text-xs">Share your link above to start earning</p>
    </div>
    @endforelse
</div>

</div>
@endsection

@section('scripts')
<script>
function copyLink(){
    navigator.clipboard.writeText(document.getElementById('ref-link').value).then(()=>{
        document.getElementById('copy-label').textContent='Copied!';
        showToast('Referral link copied!','success');
        setTimeout(()=>document.getElementById('copy-label').textContent='Copy',2000);
    });
}
</script>
@endsection
