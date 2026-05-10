@extends('layouts.app')
@section('title', 'Payment Accounts')
@section('page-title', 'Payment Accounts')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Payment Accounts</h1>
                <p class="text-on-surface-variant mt-1">
                    Only <strong>active</strong> accounts are shown to users on the Add Funds page.
                </p>
            </div>
            <a href="{{ route('admin.fund_accounts.create') }}"
               class="btn-primary px-4 py-3 rounded-xl inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span> Add Account
            </a>
        </div>

        @if(session('success'))
        <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5">
            <p class="text-tertiary font-medium">{{ session('success') }}</p>
        </div>
        @endif

        {{-- Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container">
                            <th class="px-6 py-4 text-left text-xs uppercase tracking-wider text-on-surface-variant">Name</th>
                            <th class="px-6 py-4 text-left text-xs uppercase tracking-wider text-on-surface-variant">IBAN / Account #</th>
                            <th class="px-6 py-4 text-left text-xs uppercase tracking-wider text-on-surface-variant">Notes</th>
                            <th class="px-6 py-4 text-center text-xs uppercase tracking-wider text-on-surface-variant">Active</th>
                            <th class="px-6 py-4 text-right text-xs uppercase tracking-wider text-on-surface-variant">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                        <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors" id="row-{{ $account->id }}">

                            <td class="px-6 py-4 text-on-surface font-medium">{{ $account->name }}</td>

                            <td class="px-6 py-4 text-on-surface-variant text-sm">
                                @if($account->iban)
                                    <span class="block">IBAN: {{ $account->iban }}</span>
                                @endif
                                @if($account->account_number)
                                    <span class="block">Acc: {{ $account->account_number }}</span>
                                @endif
                                @if(!$account->iban && !$account->account_number)
                                    —
                                @endif
                            </td>

                            <td class="px-6 py-4 text-on-surface-variant text-sm">
                                {{ $account->notes ? mb_strimwidth($account->notes, 0, 60, '…') : '—' }}
                            </td>

                            {{-- Toggle switch --}}
                            <td class="px-6 py-4 text-center">
                                <button
                                    type="button"
                                    onclick="toggleAccount({{ $account->id }}, this)"
                                    title="{{ $account->is_active ? 'Click to disable' : 'Click to enable' }}"
                                    class="relative inline-flex items-center w-11 h-6 rounded-full transition-colors focus:outline-none
                                           {{ $account->is_active ? 'bg-primary' : 'bg-outline/30' }}">
                                    <span class="sr-only">Toggle</span>
                                    <span class="inline-block w-4 h-4 bg-white rounded-full shadow transition-transform
                                                 {{ $account->is_active ? 'translate-x-6' : 'translate-x-1' }}"
                                          id="thumb-{{ $account->id }}">
                                    </span>
                                </button>
                                <span class="block text-[10px] mt-1 font-semibold uppercase tracking-wider
                                             {{ $account->is_active ? 'text-primary' : 'text-outline' }}"
                                      id="status-text-{{ $account->id }}">
                                    {{ $account->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="{{ route('admin.fund_accounts.edit', $account->id) }}"
                                   class="text-primary text-sm font-semibold hover:underline">Edit</a>

                                <form method="POST"
                                      action="{{ route('admin.fund_accounts.destroy', $account->id) }}"
                                      class="inline"
                                      onsubmit="return confirm('Delete this account?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-error text-sm font-semibold hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[40px] opacity-30 block mb-2">account_balance</span>
                                <p class="mb-3">No payment accounts configured yet.</p>
                                <a href="{{ route('admin.fund_accounts.create') }}"
                                   class="text-primary text-sm hover:underline">Add your first account →</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">{{ $accounts->links() }}</div>

    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleAccount(id, btn) {
    btn.disabled = true;

    fetch(`/admin/fund-accounts/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(r => r.json())
    .then(data => {
        const active = data.is_active;
        const thumb  = document.getElementById(`thumb-${id}`);
        const label  = document.getElementById(`status-text-${id}`);

        // Animate toggle
        btn.classList.toggle('bg-primary', active);
        btn.classList.toggle('bg-outline/30', !active);
        thumb.classList.toggle('translate-x-6', active);
        thumb.classList.toggle('translate-x-1', !active);
        label.textContent = active ? 'Active' : 'Disabled';
        label.classList.toggle('text-primary', active);
        label.classList.toggle('text-outline', !active);

        btn.title = active ? 'Click to disable' : 'Click to enable';
    })
    .catch(() => alert('Failed to update — please refresh and try again.'))
    .finally(() => { btn.disabled = false; });
}
</script>
@endsection

