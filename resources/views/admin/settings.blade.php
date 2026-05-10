@extends('layouts.app')

@section('title', 'Admin Settings')
@section('page-title', 'Admin Settings')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-on-surface">Admin Settings</h1>
            <p class="text-on-surface-variant mt-1">Manage platform configuration and settings</p>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5">
                <p class="text-tertiary font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- System Settings --}}
        <div class="glass-card rounded-xl p-6 mb-6">
            <h2 class="text-xl font-bold text-on-surface mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined">settings</span>
                System Configuration
            </h2>

            <div class="space-y-6">
                <div>
                    <h3 class="text-sm font-semibold text-on-surface mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button onclick="syncAllProviders()" class="flex items-center gap-3 p-4 border border-outline-variant/50 rounded-lg hover:border-primary/50 hover:bg-primary/5 transition-all">
                            <span class="material-symbols-outlined text-primary">sync</span>
                            <div class="text-left">
                                <p class="font-semibold text-on-surface">Sync All Services</p>
                                <p class="text-xs text-on-surface-variant">Update all provider services</p>
                            </div>
                        </button>

                        <button onclick="syncAllOrders()" class="flex items-center gap-3 p-4 border border-outline-variant/50 rounded-lg hover:border-primary/50 hover:bg-primary/5 transition-all">
                            <span class="material-symbols-outlined text-primary">shopping_cart</span>
                            <div class="text-left">
                                <p class="font-semibold text-on-surface">Sync Order Status</p>
                                <p class="text-xs text-on-surface-variant">Update pending order statuses</p>
                            </div>
                        </button>

                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 p-4 border border-outline-variant/50 rounded-lg hover:border-primary/50 hover:bg-primary/5 transition-all">
                            <span class="material-symbols-outlined text-primary">dashboard</span>
                            <div class="text-left">
                                <p class="font-semibold text-on-surface">Dashboard</p>
                                <p class="text-xs text-on-surface-variant">View admin dashboard</p>
                            </div>
                        </a>

                        <a href="{{ route('admin.services.index') }}" class="flex items-center gap-3 p-4 border border-outline-variant/50 rounded-lg hover:border-primary/50 hover:bg-primary/5 transition-all">
                            <span class="material-symbols-outlined text-primary">inventory_2</span>
                            <div class="text-left">
                                <p class="font-semibold text-on-surface">Services</p>
                                <p class="text-xs text-on-surface-variant">Manage all services</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Information --}}
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-bold text-on-surface mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined">info</span>
                Platform Information
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-on-surface-variant text-xs font-label-caps uppercase tracking-widest mb-2">Laravel Version</p>
                    <p class="text-on-surface font-semibold">{{ app()->version() }}</p>
                </div>

                <div>
                    <p class="text-on-surface-variant text-xs font-label-caps uppercase tracking-widest mb-2">PHP Version</p>
                    <p class="text-on-surface font-semibold">{{ PHP_VERSION }}</p>
                </div>

                <div>
                    <p class="text-on-surface-variant text-xs font-label-caps uppercase tracking-widest mb-2">Current Time</p>
                    <p class="text-on-surface font-semibold">{{ now()->format('d M Y H:i:s') }}</p>
                </div>

                <div>
                    <p class="text-on-surface-variant text-xs font-label-caps uppercase tracking-widest mb-2">Environment</p>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-primary/20 text-primary uppercase">{{ config('app.env') }}</span>
                </div>
            </div>
        </div>

        {{-- WhatsApp Settings --}}
        <div class="glass-card rounded-xl p-6 mt-6">
            <h2 class="text-xl font-bold text-on-surface mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined" style="color:#22c55e">chat</span>
                WhatsApp Contact
            </h2>
            <p class="text-on-surface-variant text-sm mb-6">
                Users will see a WhatsApp button on the Add Funds page to contact you directly for faster payment approval.
            </p>

            @if(session('success'))
            <div class="p-3 rounded-lg mb-4 border-l-4 border-tertiary bg-tertiary/5">
                <p class="text-tertiary font-medium text-sm">{{ session('success') }}</p>
            </div>
            @endif

            <form action="{{ route('admin.settings.save') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-on-surface mb-2">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" value="{{ $whatsappNumber }}"
                               class="glass-input w-full" placeholder="923001234567 (country code, no +)">
                        <p class="text-on-surface-variant text-xs mt-1">Include country code without + (e.g. 923001234567)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-on-surface mb-2">Default Message</label>
                        <input type="text" name="whatsapp_message" value="{{ $whatsappMessage }}"
                               class="glass-input w-full" placeholder="Hi, I submitted a fund request...">
                        <p class="text-on-surface-variant text-xs mt-1">Pre-filled message when user taps Chat Now</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn-primary px-6 py-2 rounded-lg font-semibold">Save Settings</button>
                </div>
            </form>
        </div>

        {{-- Danger Zone --}}
        <div class="glass-card rounded-xl p-6 border border-error/20 mt-6 bg-error/2">
            <h2 class="text-xl font-bold text-error mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined">warning</span>
                Danger Zone
            </h2>

            <p class="text-on-surface-variant text-sm mb-4">These actions are irreversible. Use with caution.</p>

            <div class="space-y-3">
                <button onclick="if(confirm('Clear all cache? This will temporarily impact performance.')) { clearCache() }" 
                        class="w-full px-4 py-3 rounded-lg border border-error/50 text-error hover:bg-error/5 font-semibold transition-all">
                    Clear Application Cache
                </button>

                <button onclick="if(confirm('Clear all logs? This is permanent.')) { clearLogs() }" 
                        class="w-full px-4 py-3 rounded-lg border border-error/50 text-error hover:bg-error/5 font-semibold transition-all">
                    Clear All Logs
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function syncAllProviders() {
    fetch('/admin/sync-services', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
        .then(r => r.json())
        .then(d => alert(d.message))
        .catch(e => alert('Error: ' + e.message));
}

function syncAllOrders() {
    fetch('/admin/sync-orders', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
        .then(r => r.json())
        .then(d => alert(d.message))
        .catch(e => alert('Error: ' + e.message));
}

function clearCache() {
    alert('Cache clearing not implemented yet');
}

function clearLogs() {
    alert('Log clearing not implemented yet');
}
</script>
@endsection
