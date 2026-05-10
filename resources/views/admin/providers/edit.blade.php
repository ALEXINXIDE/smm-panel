@extends('layouts.app')

@section('title', 'Edit API Provider')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-on-surface">Edit API Provider</h1>
            <p class="text-on-surface-variant mt-1">Update provider settings</p>
        </div>

        @if ($errors->has('error'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-error bg-error/5">
                <p class="text-error font-medium">{{ $errors->first('error') }}</p>
            </div>
        @endif

        <div class="glass-card p-6 rounded-xl">
            <form action="{{ route('admin.providers.update', $provider->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-on-surface mb-2">Provider Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $provider->name) }}"
                               class="glass-input w-full" required>
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- URL -->
                    <div>
                        <label for="url" class="block text-sm font-medium text-on-surface mb-2">API URL</label>
                        <input type="url" id="url" name="url" value="{{ old('url', $provider->url) }}"
                               class="glass-input w-full" required>
                        @error('url')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- API Key -->
                    <div>
                        <label for="api_key" class="block text-sm font-medium text-on-surface mb-2">API Key</label>
                        <input type="password" id="api_key" name="api_key" value="{{ old('api_key', $provider->api_key) }}"
                               class="glass-input w-full">
                        <p class="text-on-surface-variant text-xs mt-1">Leave blank to keep current key</p>
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
                               value="{{ old('percentage_increase', $provider->percentage_increase) }}" 
                               step="0.01" min="0" max="10000" class="glass-input w-full" required>
                        @error('percentage_increase')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-on-surface mb-2">Status</label>
                        <select id="status" name="status" class="glass-input w-full" required>
                            <option value="active" {{ old('status', $provider->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $provider->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <a href="{{ route('admin.providers.index') }}"
                       class="btn-ghost px-6 py-3 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary px-6 py-3 rounded-lg">
                        Update Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Edit API Provider')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-on-surface">Edit API Provider</h1>
            <p class="text-on-surface-variant mt-1">Update provider settings</p>
        </div>

        @if ($errors->has('error'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-error bg-error/5">
                <p class="text-error font-medium">{{ $errors->first('error') }}</p>
            </div>
        @endif

        <div class="glass-card p-6 rounded-xl">
            <form action="{{ route('admin.providers.update', $provider->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-on-surface mb-2">Provider Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $provider->name) }}"
                               class="glass-input w-full" required>
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- URL -->
                    <div>
                        <label for="url" class="block text-sm font-medium text-on-surface mb-2">API URL</label>
                        <input type="url" id="url" name="url" value="{{ old('url', $provider->url) }}"
                               class="glass-input w-full" required>
                        @error('url')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- API Key -->
                    <div>
                        <label for="api_key" class="block text-sm font-medium text-on-surface mb-2">API Key</label>
                        <input type="password" id="api_key" name="api_key" value="{{ old('api_key', $provider->api_key) }}"
                               class="glass-input w-full">
                        <p class="text-on-surface-variant text-xs mt-1">Leave blank to keep current key</p>
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
                               value="{{ old('percentage_increase', $provider->percentage_increase) }}" 
                               step="0.01" min="0" max="10000" class="glass-input w-full" required>
                        @error('percentage_increase')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-on-surface mb-2">Status</label>
                        <select id="status" name="status" class="glass-input w-full" required>
                            <option value="active" {{ old('status', $provider->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $provider->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <a href="{{ route('admin.providers.index') }}"
                       class="btn-ghost px-6 py-3 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary px-6 py-3 rounded-lg">
                        Update Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
