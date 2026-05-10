@extends('layouts.app')
@section('title', 'Fund Requests')
@section('page-title', 'Fund Requests')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Fund Requests</h1>
                <p class="text-on-surface-variant mt-1">Review and approve manual payment submissions</p>
            </div>
            <a href="{{ route('admin.payment-accounts.index') }}"
               class="btn-ghost px-4 py-2 rounded-lg text-sm font-semibold">← Manage Accounts</a>
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

        {{-- Filter --}}
        <div class="glass-card p-4 rounded-xl mb-6">
            <form action="{{ route('admin.fund-requests.index') }}" method="GET" class="flex gap-3">
                <select name="status" class="glass-input">
                    <option value="">All statuses</option>
                    <option value="pending"  {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Approved</option>
                    <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
                </select>
                <button type="submit" class="btn-primary px-4 py-2 rounded-lg text-sm">Filter</button>
            </form>
        </div>

        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container">
                            <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">User</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Account</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Amount</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">TXN ID</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Submitted</th>
                            <th class="px-4 py-4 text-right text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
                            <td class="px-4 py-4">
                                <p class="text-on-surface font-medium">{{ $req->user->name }}</p>
                                <p class="text-on-surface-variant text-xs">{{ $req->user->email }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-on-surface text-sm font-semibold">{{ $req->paymentAccount->name }}</p>
                                <p class="text-on-surface-variant text-xs font-mono">{{ $req->paymentAccount->account_number }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-on-surface font-bold">₨{{ number_format($req->amount, 0) }}</p>
                                <p class="text-primary text-xs font-semibold">${{ number_format($req->usd_amount, 4) }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-on-surface font-mono text-sm bg-surface-container px-2 py-1 rounded inline-block">
                                    {{ $req->transaction_id }}
                                </p>
                            </td>
                            <td class="px-4 py-4">
                                @if($req->status === 'pending')
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400">Pending</span>
                                @elseif($req->status === 'approved')
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-tertiary/20 text-tertiary">Approved</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-error/20 text-error">Rejected</span>
                                @endif
                                @if($req->admin_note)
                                    <p class="text-on-surface-variant text-xs mt-1">{{ $req->admin_note }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-on-surface-variant text-sm">{{ $req->created_at->format('d M Y') }}</p>
                                <p class="text-on-surface-variant text-xs">{{ $req->created_at->format('H:i') }}</p>
                            </td>
                            <td class="px-4 py-4 text-right">
                                @if($req->status === 'pending')
                                <div class="flex justify-end gap-2">
                                    <form action="{{ route('admin.fund-requests.approve', $req) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="admin_note" value="Verified">
                                        <button type="submit"
                                                onclick="return confirm('Approve ₨{{ number_format($req->amount,0) }} from {{ addslashes($req->user->name) }}?\nTXN: {{ $req->transaction_id }}')"
                                                class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-tertiary/20 text-tertiary hover:bg-tertiary/30 transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    <button onclick="openRejectModal({{ $req->id }})"
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-error/20 text-error hover:bg-error/30 transition-colors">
                                        Reject
                                    </button>
                                </div>
                                @else
                                    <span class="text-on-surface-variant text-xs">{{ $req->reviewed_at?->format('d M H:i') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <p class="text-on-surface-variant text-sm">No fund requests found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $requests->links() }}</div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="glass-card p-6 rounded-xl max-w-sm w-full mx-4">
        <h3 class="text-lg font-bold text-on-surface mb-4">Reject Fund Request</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-on-surface mb-2">Reason for rejection</label>
                <textarea name="admin_note" rows="3" class="glass-input w-full"
                          placeholder="e.g. Transaction ID not found in our records"
                          required minlength="5"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeRejectModal()"
                        class="btn-ghost px-4 py-2 rounded-lg flex-1">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 rounded-lg flex-1 bg-error/20 text-error font-semibold hover:bg-error/30 transition-colors">
                    Reject
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id) {
    document.getElementById('rejectForm').action = `/admin/fund-requests/${id}/reject`;
    document.getElementById('rejectModal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
document.getElementById('rejectModal').addEventListener('click', e => {
    if (e.target.id === 'rejectModal') closeRejectModal();
});
</script>
@endsection
