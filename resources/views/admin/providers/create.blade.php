@extends('layouts.app')

@section('title', 'Create API Provider')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-on-surface">Create API Provider</h1>
            <p class="text-on-surface-variant mt-1">Add a new social media API provider</p>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <form action="{{ route('admin.providers.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-on-surface mb-2">Provider Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="glass-input w-full" placeholder="e.g., SMMPanel Pro" required>
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- URL -->
                    <div>
                        <label for="url" class="block text-sm font-medium text-on-surface mb-2">API URL</label>
                        <input type="url" id="url" name="url" value="{{ old('url') }}"
                               class="glass-input w-full" placeholder="https://api.example.com" required>
                        @error('url')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- API Key -->
                    <div>
                        <label for="api_key" class="block text-sm font-medium text-on-surface mb-2">API Key</label>
                        <input type="password" id="api_key" name="api_key" value="{{ old('api_key') }}"
                               class="glass-input w-full" placeholder="Your API key" required>
                        @error('api_key')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Percentage Increase -->
                    <div>
                        <label for="percentage_increase" class="block text-sm font-medium text-on-surface mb-2">
                            Price Increase (%)
                        </label>
                        <input type="number" id="percentage_increase" name="percentage_increase"
                               value="{{ old('percentage_increase', 0) }}" step="0.01" min="0" max="10000"
                               class="glass-input w-full" placeholder="0.00" required>
                        @error('percentage_increase')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-on-surface-variant text-sm mt-1">
                            Percentage to increase prices above the provider's rates
                        </p>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <a href="{{ route('admin.providers.index') }}"
                       class="btn-ghost px-6 py-3 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary px-6 py-3 rounded-lg">
                        Create Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@extends('layouts.app')

@section('title', 'Create API Provider')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-on-surface">Create API Provider</h1>
            <p class="text-on-surface-variant mt-1">Add a new social media API provider</p>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <form action="{{ route('admin.providers.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-on-surface mb-2">Provider Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="glass-input w-full" placeholder="e.g., SMMPanel Pro" required>
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- URL -->
                    <div>
                        <label for="url" class="block text-sm font-medium text-on-surface mb-2">API URL</label>
                        <input type="url" id="url" name="url" value="{{ old('url') }}"
                               class="glass-input w-full" placeholder="https://api.example.com" required>
                        @error('url')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- API Key -->
                    <div>
                        <label for="api_key" class="block text-sm font-medium text-on-surface mb-2">API Key</label>
                        <input type="password" id="api_key" name="api_key" value="{{ old('api_key') }}"
                               class="glass-input w-full" placeholder="Your API key" required>
                        @error('api_key')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Percentage Increase -->
                    <div>
                        <label for="percentage_increase" class="block text-sm font-medium text-on-surface mb-2">
                            Price Increase (%)
                        </label>
                        <input type="number" id="percentage_increase" name="percentage_increase"
                               value="{{ old('percentage_increase', 0) }}" step="0.01" min="0" max="10000"
                               class="glass-input w-full" placeholder="0.00" required>
                        @error('percentage_increase')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-on-surface-variant text-sm mt-1">
                            Percentage to increase prices above the provider's rates
                        </p>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <a href="{{ route('admin.providers.index') }}"
                       class="btn-ghost px-6 py-3 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary px-6 py-3 rounded-lg">
                        Create Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection