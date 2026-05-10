@extends('layouts.app')

@section('title', 'Ticket Detail')
@section('page-title', 'Ticket Detail')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Ticket #{{ $ticket->id }}</h1>
                <p class="text-on-surface-variant mt-1">{{ $ticket->subject }}</p>
            </div>
            <a href="{{ route('admin.tickets.index') }}" class="text-primary hover:text-primary/80 font-semibold">Back to tickets</a>
        </div>

        @if(session('success'))
            <div class="glass-card p-4 rounded-xl mb-6 border-l-4 border-tertiary bg-tertiary/5">
                <p class="text-tertiary font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="glass-card rounded-xl p-6 mb-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <p class="text-on-surface-variant text-sm uppercase tracking-wider">User</p>
                    <p class="text-on-surface font-semibold">{{ $ticket->user->name }}</p>
                    <p class="text-on-surface-variant text-sm">{{ $ticket->user->email }}</p>
                </div>
                <div class="text-right">
                    <p class="text-on-surface-variant text-sm">Created</p>
                    <p class="text-on-surface font-semibold">{{ $ticket->created_at->format('d M Y H:i') }}</p>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $ticket->status === 'open' ? 'bg-tertiary/20 text-tertiary' : ($ticket->status === 'pending' ? 'bg-yellow/20 text-yellow' : 'bg-outline/20 text-outline') }} uppercase">{{ $ticket->status }}</span>
                </div>
            </div>

            <div class="space-y-4 mb-8">
                @foreach($ticket->messages as $message)
                    <div class="p-4 rounded-2xl border {{ $message->is_admin ? 'border-secondary/20 bg-secondary/5' : 'border-outline-variant/30 bg-surface-container' }}">
                        <div class="flex items-center justify-between gap-4 mb-3">
                            <div>
                                <p class="text-sm font-semibold text-on-surface">{{ $message->is_admin ? 'Support Team' : $message->user->name }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $message->created_at->format('d M Y H:i') }}</p>
                            </div>
                            <span class="text-xs text-on-surface-variant">{{ $message->is_admin ? 'Admin' : 'User' }}</span>
                        </div>
                        <p class="text-on-surface text-sm whitespace-pre-line">{{ $message->message }}</p>
                    </div>
                @endforeach
            </div>

            @if($ticket->status !== 'closed')
                <div class="space-y-4">
                    <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-on-surface mb-2">Reply Message</label>
                            <textarea name="message" rows="5" required class="glass-input w-full bg-transparent border border-outline-variant/40 rounded-xl p-4 text-sm text-on-surface" placeholder="Write your response..."></textarea>
                        </div>
                        <button type="submit" class="btn-primary px-5 py-3 rounded-xl">Send Reply</button>
                    </form>

                    <form action="{{ route('admin.tickets.close', $ticket->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-ghost px-5 py-3 rounded-xl border border-outline-variant/60 text-error hover:bg-error/10">Close Ticket</button>
                    </form>
                </div>
            @else
                <p class="text-on-surface-variant text-sm">This ticket is already closed.</p>
            @endif
        </div>
    </div>
</div>
@endsection
