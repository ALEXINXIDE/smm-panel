@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Users</h1>
                <p class="text-on-surface-variant mt-1">Manage all platform users</p>
            </div>
        </div>

        {{-- Search --}}
        <div class="glass-card p-4 rounded-xl mb-6">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex gap-4">
                <input type="text" name="search" placeholder="Search by name or email..." 
                       value="{{ request('search') }}" class="glass-input flex-1">
                <button type="submit" class="btn-primary px-6 rounded-lg">Search</button>
            </form>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5">
                <p class="text-tertiary font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Users Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container">
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">User</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Email</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Orders</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Status</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Joined</p>
                            </th>
                            <th class="px-6 py-4 text-right">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-primary flex items-center justify-center text-white font-bold text-xs">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <p class="text-on-surface font-medium">{{ $user->name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ $user->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-semibold">{{ $user->orders_count }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->status === 'active' ? 'bg-tertiary/20 text-tertiary' : 'bg-error/20 text-error' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ $user->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if ($user->status === 'active')
                                            <form action="{{ route('admin.users.ban', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Ban this user?')" 
                                                        class="text-error hover:text-error/80 font-semibold text-sm transition-colors">
                                                    Ban
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.users.unban', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-tertiary hover:text-tertiary/80 font-semibold text-sm transition-colors">
                                                    Unban
                                                </button>
                                            </form>
                                        @endif
                                        <button onclick="openAddFundsModal({{ $user->id }}, '{{ $user->name }}')" 
                                                class="text-primary hover:text-primary/80 font-semibold text-sm transition-colors">
                                            Add Funds
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40 block mb-2">people</span>
                                    <p class="text-on-surface-variant text-sm">No users found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</div>

{{-- Add Funds Modal --}}
<div id="addFundsModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="glass-card p-6 rounded-xl max-w-sm w-full mx-4">
        <h3 class="text-xl font-bold text-on-surface mb-4">Add Funds</h3>
        <form id="addFundsForm" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-2">Amount (PKR)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" class="glass-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-2">Reason</label>
                    <textarea name="reason" rows="3" class="glass-input w-full" placeholder="Reason for adding funds..." required minlength="5"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeAddFundsModal()" class="btn-ghost px-4 py-2 rounded-lg flex-1">Cancel</button>
                <button type="submit" class="btn-primary px-4 py-2 rounded-lg flex-1">Add Funds</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddFundsModal(userId, userName) {
    const modal = document.getElementById('addFundsModal');
    const form = document.getElementById('addFundsForm');
    form.action = `/admin/users/${userId}/add-funds`;
    modal.classList.remove('hidden');
}

function closeAddFundsModal() {
    document.getElementById('addFundsModal').classList.add('hidden');
}

document.getElementById('addFundsModal').addEventListener('click', (e) => {
    if (e.target.id === 'addFundsModal') closeAddFundsModal();
});
</script>
@endsection
@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Users</h1>
                <p class="text-on-surface-variant mt-1">Manage all platform users</p>
            </div>
        </div>

        {{-- Search --}}
        <div class="glass-card p-4 rounded-xl mb-6">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex gap-4">
                <input type="text" name="search" placeholder="Search by name or email..." 
                       value="{{ request('search') }}" class="glass-input flex-1">
                <button type="submit" class="btn-primary px-6 rounded-lg">Search</button>
            </form>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5">
                <p class="text-tertiary font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Users Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container">
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">User</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Email</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Orders</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Status</p>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Joined</p>
                            </th>
                            <th class="px-6 py-4 text-right">
                                <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-primary flex items-center justify-center text-white font-bold text-xs">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <p class="text-on-surface font-medium">{{ $user->name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ $user->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface font-semibold">{{ $user->orders_count }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->status === 'active' ? 'bg-tertiary/20 text-tertiary' : 'bg-error/20 text-error' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-on-surface-variant text-sm">{{ $user->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if ($user->status === 'active')
                                            <form action="{{ route('admin.users.ban', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Ban this user?')" 
                                                        class="text-error hover:text-error/80 font-semibold text-sm transition-colors">
                                                    Ban
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.users.unban', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-tertiary hover:text-tertiary/80 font-semibold text-sm transition-colors">
                                                    Unban
                                                </button>
                                            </form>
                                        @endif
                                        <button onclick="openAddFundsModal({{ $user->id }}, '{{ $user->name }}')" 
                                                class="text-primary hover:text-primary/80 font-semibold text-sm transition-colors">
                                            Add Funds
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant opacity-40 block mb-2">people</span>
                                    <p class="text-on-surface-variant text-sm">No users found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</div>

{{-- Add Funds Modal --}}
<div id="addFundsModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="glass-card p-6 rounded-xl max-w-sm w-full mx-4">
        <h3 class="text-xl font-bold text-on-surface mb-4">Add Funds</h3>
        <form id="addFundsForm" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-2">Amount (PKR)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" class="glass-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-2">Reason</label>
                    <textarea name="reason" rows="3" class="glass-input w-full" placeholder="Reason for adding funds..." required minlength="5"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeAddFundsModal()" class="btn-ghost px-4 py-2 rounded-lg flex-1">Cancel</button>
                <button type="submit" class="btn-primary px-4 py-2 rounded-lg flex-1">Add Funds</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddFundsModal(userId, userName) {
    const modal = document.getElementById('addFundsModal');
    const form = document.getElementById('addFundsForm');
    form.action = `/admin/users/${userId}/add-funds`;
    modal.classList.remove('hidden');
}

function closeAddFundsModal() {
    document.getElementById('addFundsModal').classList.add('hidden');
}

document.getElementById('addFundsModal').addEventListener('click', (e) => {
    if (e.target.id === 'addFundsModal') closeAddFundsModal();
});
</script>
@endsection
