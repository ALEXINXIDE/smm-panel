@extends('layouts.app')
@section('title', 'Support Center')
@section('page-title', 'Support Center')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">

    {{-- Ticket list --}}
    <div class="lg:col-span-1 fade-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-h3 text-h3 text-on-surface">Active Tickets</h2>
            <a href="{{ route('tickets.create') }}" class="bg-gradient-primary text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:brightness-110 transition-all flex items-center gap-1 neon-glow-primary">
                <span class="material-symbols-outlined text-[14px]">add</span> New
            </a>
        </div>
        <div class="space-y-2">
            @forelse($tickets as $ticket)
            <a href="{{ route('tickets.show', $ticket->id) }}"
                class="glass-card rounded-xl p-4 block hover:border-primary/50 transition-all group {{ request()->route('ticket')?->id == $ticket->id ? 'border-primary/60' : '' }}">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <h3 class="font-medium text-on-surface text-sm leading-tight group-hover:text-primary transition-colors truncate">{{ $ticket->subject }}</h3>
                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase border flex-shrink-0
                        {{ $ticket->status === 'open' ? 'bg-tertiary/10 text-tertiary border-tertiary/30' : ($ticket->status === 'pending' ? 'bg-[#fcd34d]/10 text-[#fcd34d] border-[#fcd34d]/30' : 'bg-outline/10 text-outline border-outline/30') }}">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </div>
                <p class="text-outline text-xs truncate">{{ str($ticket->message)->limit(60) }}</p>
                <p class="text-outline text-[10px] mt-2">{{ $ticket->created_at->diffForHumans() }}</p>
            </a>
            @empty
            <div class="glass-card rounded-xl p-8 text-center text-outline">
                <span class="material-symbols-outlined text-[40px] block mb-2 opacity-20">confirmation_number</span>
                <p class="text-sm">No tickets yet</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Ticket detail / new ticket form --}}
    <div class="lg:col-span-2 fade-up">
        @if(isset($ticket))
        {{-- View ticket --}}
        <div class="glass-card rounded-xl p-md mb-4">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="font-h3 text-h3 text-on-surface">{{ $ticket->subject }}</h2>
                    <p class="text-outline text-xs mt-1">Ticket #{{ $ticket->id }} · Opened {{ $ticket->created_at->diffForHumans() }}</p>
                </div>
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase border
                    {{ $ticket->status === 'open' ? 'bg-tertiary/10 text-tertiary border-tertiary/30' : 'bg-outline/10 text-outline border-outline/30' }}">
                    {{ ucfirst($ticket->status) }}
                </span>
            </div>
            {{-- Messages --}}
            <div class="space-y-4 mb-6 max-h-96 overflow-y-auto pr-2">
                @foreach($ticket->messages as $msg)
                <div class="flex gap-3 {{ $msg->is_admin ? '' : 'flex-row-reverse' }}">
                    <div class="w-8 h-8 rounded-full {{ $msg->is_admin ? 'bg-secondary/20 border border-secondary/30' : 'bg-gradient-primary' }} flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                        {{ $msg->is_admin ? 'S' : strtoupper(substr(auth()->user()->name,0,1)) }}
                    </div>
                    <div class="flex-1 {{ $msg->is_admin ? '' : 'text-right' }}">
                        <div class="{{ $msg->is_admin ? 'bg-surface-container-high rounded-xl rounded-tl-none' : 'bg-primary/10 rounded-xl rounded-tr-none border border-primary/20' }} p-3">
                            <p class="text-on-surface text-sm">{{ $msg->message }}</p>
                        </div>
                        <p class="text-outline text-[10px] mt-1">{{ $msg->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            {{-- Reply form --}}
            @if($ticket->status !== 'closed')
            <form method="POST" action="{{ route('tickets.reply', $ticket->id) }}">
                @csrf
                <div class="flex gap-3">
                    <input type="text" name="message" placeholder="Type your reply..." required
                        class="flex-1 glass-input bg-transparent py-2.5 px-4 rounded-xl border border-outline-variant/40 focus:border-primary text-sm transition-colors">
                    <button type="submit" class="bg-gradient-primary text-white px-5 py-2.5 rounded-xl font-semibold text-sm hover:brightness-110 transition-all neon-glow-primary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">send</span>
                    </button>
                </div>
            </form>
            @else
            <p class="text-center text-outline text-sm">This ticket is closed. <a href="{{ route('tickets.create') }}" class="text-primary hover:underline">Open a new ticket</a></p>
            @endif
        </div>
        @else
        {{-- New ticket form --}}
        <div class="glass-card rounded-xl p-md">
            <h2 class="font-h3 text-h3 text-on-surface mb-1">Open a New Ticket</h2>
            <p class="text-on-surface-variant text-sm mb-6">We typically respond within 2-4 hours</p>
            <form method="POST" action="{{ route('tickets.store') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="font-label-caps text-label-caps text-outline">Subject</label>
                    <input type="text" name="subject" placeholder="Brief description of your issue" required
                        class="w-full glass-input bg-transparent py-2.5 px-4 rounded-xl border border-outline-variant/40 focus:border-primary text-sm transition-colors placeholder:text-outline/50">
                </div>
                <div class="space-y-2">
                    <label class="font-label-caps text-label-caps text-outline">Category</label>
                    <div class="relative">
                        <select name="category" class="w-full glass-input bg-transparent py-2.5 pl-4 pr-10 rounded-xl border border-outline-variant/40 focus:border-primary text-sm appearance-none">
                            <option value="order">Order Issue</option>
                            <option value="payment">Payment / Funds</option>
                            <option value="technical">Technical Problem</option>
                            <option value="other">Other</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-2.5 text-outline pointer-events-none text-[18px]">expand_more</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="font-label-caps text-label-caps text-outline">Message</label>
                    <textarea name="message" rows="5" placeholder="Describe your issue in detail..." required
                        class="w-full glass-input bg-transparent py-2.5 px-4 rounded-xl border border-outline-variant/40 focus:border-primary text-sm transition-colors placeholder:text-outline/50 resize-none"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="font-label-caps text-label-caps text-outline">Related Order ID (optional)</label>
                    <input type="number" name="order_id" placeholder="e.g. 12345"
                        class="w-full glass-input bg-transparent py-2.5 px-4 rounded-xl border border-outline-variant/40 focus:border-primary text-sm transition-colors placeholder:text-outline/50">
                </div>
                <button type="submit" class="w-full bg-gradient-primary text-white font-semibold py-3.5 rounded-xl neon-glow-primary hover:brightness-110 transition-all flex items-center justify-center gap-2 text-sm">
                    <span class="material-symbols-outlined text-[18px]">send</span> Submit Ticket
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
