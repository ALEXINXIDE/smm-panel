@extends('layouts.app')
@section('title', 'Orders')
@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-on-surface mb-6">Manage Orders</h1>

        <div class="glass-card rounded-xl overflow-hidden">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/30 bg-surface-container">
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Order ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr class="border-b border-outline-variant/30">
                        <td class="px-6 py-4">#{{ $order->id }}</td>
                        <td class="px-6 py-4">{{ $order->user->name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-primary/20 text-primary">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            {{-- FIXED: Route name changed to 'admin.orders.status' and method set to PATCH --}}
                            <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="glass-input text-xs">
                                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
