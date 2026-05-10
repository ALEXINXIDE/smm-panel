@extends('layouts.app')

@section('title', 'Fund Requests')
@section('page-title', 'Fund Requests')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Fund Requests</h1>
                <p class="text-on-surface-variant mt-1">Review and process manual deposit proofs</p>
            </div>
        </div>

        @if(session('success'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5">
                <p class="text-tertiary font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-error bg-error/5">
                @foreach($errors->all() as $error)
                    <p class="text-error font-medium">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-variant/50 text-on-surface-variant uppercase text-xs">
                        <tr>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Method / Account</th>
                            <th class="px-6 py-4">Amount (PKR)</th>
                            <th class="px-6 py-4">USD to Credit</th>
                            <th class="px-6 py-4">Transaction ID</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @forelse($requests as $request)
                            <tr class="hover:bg-surface-variant/10 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-on-surface">{{ $request->user->name }}</div>
                                    <div class="text-xs text-on-surface-variant">{{ $request->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-on-surface">
                                    <span class="capitalize">{{ $request->paymentAccount->type }}</span>
                                    <div class="text-xs text-on-surface-variant">{{ $request->paymentAccount->name }}</div>
                                </td>
                                <td class="px-6 py-4 font-medium text-on-surface">
                                    ₨{{ number_format($request->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 font-bold text-primary">
                                    ${{ number_format($request->usd_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 font-mono text-sm text-on-surface-variant">
                                    {{ $request->transaction_id }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-warning/20 text-warning">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        {{-- Approve Button --}}
                                        <form action="{{ route('admin.fund-requests.approve', $request) }}" method="POST" onsubmit="return confirm('Credit ${{ number_format($request->usd_amount, 2) }} to this user?')">
                                            @csrf
                                            <button type="submit" class="bg-primary text-on-primary px-3 py-1.5 rounded-lg text-xs font-bold hover:opacity-90">
                                                Approve
                                            </button>
                                        </form>

                                        {{-- Reject Button (Triggers a simple prompt for the reason) --}}
                                        <button onclick="rejectRequest({{ $request->id }})" class="bg-error/10 text-error px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-error/20">
                                            Reject
                                        </button>

                                        <form id="reject-form-{{ $request->id }}" action="{{ route('admin.fund-requests.reject', $request) }}" method="POST" class="hidden">
                                            @csrf
                                            <input type="hidden" name="admin_note" id="reject-note-{{ $request->id }}">
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40 block mb-2">payments</span>
                                    <p class="text-on-surface-variant text-sm">No pending fund requests found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function rejectRequest(id) {
    const note = prompt("Please enter a reason for rejection (min 5 chars):");
    if (note && note.length >= 5) {
        document.getElementById('reject-note-' + id).value = note;
        document.getElementById('reject-form-' + id).submit();
    } else if (note) {
        alert("Reason is too short.");
    }
}
</script>
@endsection
