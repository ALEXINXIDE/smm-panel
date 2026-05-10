@extends('layouts.app')
@section('title', 'Support Tickets')
@section('page-title', 'Support Tickets')

@section('content')
<div class="flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-on-surface">Support Tickets</h1>
                <p class="text-on-surface-variant mt-1">Manage customer support requests</p>
            </div>
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
            <form action="{{ route('admin.tickets.index') }}" method="GET" class="flex gap-3">
                <select name="status" class="glass-input">
                    <option value="">All statuses</option>
                    <option value="open"    {{ request('status')=='open'   ?'selected':'' }}>Open</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="closed"  {{ request('status')=='closed' ?'selected':'' }}>Closed</option>
                </select>
                <button type="submit" class="btn-primary px-4 py-2 rounded-lg text-sm">Filter</button>
            </form>
        </div>

        {{-- Tickets Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container">
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">#</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Subject</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Created</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-on-surface font-semibold">#{{ $ticket->id }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-on-surface font-medium">{{ $ticket->user->name }}</p>
                                <p class="text-on-surface-variant text-xs">{{ $ticket->user->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-on-surface truncate max-w-xs">{{ $ticket->subject }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $ticket->status === 'open'    ? 'bg-primary/20 text-primary'        :
                                      ($ticket->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' :
                                                                       'bg-outline/20 text-outline') }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-on-surface-variant text-sm">{{ $ticket->created_at->format('d M Y H:i') }}</p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button onclick='openTicketModal(
                                    {{ $ticket->id }},
                                    @json($ticket->subject),
                                    @json($ticket->message),
                                    @json($ticket->status),
                                    {!! json_encode($ticket->messages->map(fn($m) => [
                                        "msg"      => $m->message,
                                        "is_admin" => $m->is_admin,
                                        "name"     => $m->user->name ?? "Admin",
                                        "time"     => $m->created_at->format("d M H:i"),
                                    ])) !!}
                                )'
                                class="text-primary hover:text-primary/80 font-semibold text-sm transition-colors">
                                    View & Reply
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-on-surface-variant text-sm">No tickets found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $tickets->links() }}</div>
    </div>
</div>

{{-- Ticket Detail Modal --}}
<div id="ticketModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
    <div class="glass-card rounded-xl w-full max-w-2xl flex flex-col" style="max-height:90vh">

        {{-- Header --}}
        <div class="flex items-start justify-between p-6 border-b border-outline-variant/30 flex-shrink-0">
            <div>
                <h3 class="text-lg font-bold text-on-surface" id="modal-subject"></h3>
                <p class="text-on-surface-variant text-xs mt-1" id="modal-meta"></p>
            </div>
            <button onclick="closeTicketModal()" class="text-on-surface-variant hover:text-on-surface ml-4 flex-shrink-0">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        {{-- Messages --}}
        <div id="modal-messages" class="flex-1 overflow-y-auto p-6 space-y-4"></div>

        {{-- Reply + Close --}}
        <div class="p-6 border-t border-outline-variant/30 flex-shrink-0" id="modal-actions">
            <form id="replyForm" method="POST">
                @csrf
                <textarea name="message" id="reply-textarea" rows="3" class="glass-input w-full mb-3"
                          placeholder="Type your reply..." required minlength="2"></textarea>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary px-5 py-2 rounded-lg font-semibold flex-1">
                        Send Reply
                    </button>
                    <button type="button" id="close-ticket-btn"
                            class="px-5 py-2 rounded-lg font-semibold bg-outline/20 text-on-surface-variant hover:bg-error/20 hover:text-error transition-colors">
                        Close Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Hidden close-ticket form --}}
<form id="closeTicketForm" method="POST" class="hidden">@csrf</form>

<script>
function openTicketModal(id, subject, message, status, messages) {
    document.getElementById('modal-subject').textContent = subject + ' #' + id;
    document.getElementById('modal-meta').textContent    = 'Status: ' + status.charAt(0).toUpperCase() + status.slice(1);
    
    // Updated path to match your admin.tickets.reply route
    document.getElementById('replyForm').action          = '/admin/tickets/' + id + '/reply';

    const container = document.getElementById('modal-messages');
    container.innerHTML = '';

    // Original message
    const orig = document.createElement('div');
    orig.className = 'flex gap-3 flex-row-reverse';
    orig.innerHTML = `
        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs flex-shrink-0">U</div>
        <div class="bg-surface-container rounded-xl p-3 max-w-md ml-auto">
            <p class="text-on-surface text-sm">${escHtml(message)}</p>
            <p class="text-on-surface-variant text-xs mt-1">Original message</p>
        </div>`;
    container.appendChild(orig);

    messages.forEach(m => {
        const div = document.createElement('div');
        div.className = 'flex gap-3' + (m.is_admin ? '' : ' flex-row-reverse');
        div.innerHTML = `
            <div class="w-8 h-8 rounded-full ${m.is_admin 
                ? 'bg-secondary/20 border border-secondary/30' 
                : 'bg-primary'} flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                ${m.is_admin ? 'A' : 'U'}
            </div>
            <div class="${m.is_admin 
                ? 'bg-primary/10 border border-primary/20' 
                : 'bg-surface-container'} rounded-xl p-3 max-w-md ${m.is_admin ? '' : 'ml-auto'}">
                <p class="text-on-surface text-sm">${escHtml(m.msg)}</p>
                <p class="text-on-surface-variant text-xs mt-1">${escHtml(m.name)} · ${escHtml(m.time)}</p>
            </div>`;
        container.appendChild(div);
    });

    setTimeout(() => { container.scrollTop = container.scrollHeight; }, 100);

    document.getElementById('modal-actions').style.display = status === 'closed' ? 'none' : 'block';

    document.getElementById('close-ticket-btn').onclick = function () {
        if (!confirm('Close this ticket?')) return;
        const f = document.getElementById('closeTicketForm');
        f.action = '/admin/tickets/' + id + '/close';
        f.submit();
    };

    document.getElementById('ticketModal').classList.remove('hidden');
    if (status !== 'closed') document.getElementById('reply-textarea').focus();
}

function closeTicketModal() {
    document.getElementById('ticketModal').classList.add('hidden');
}

function escHtml(str) {
    if (!str) return "";
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

document.getElementById('ticketModal').addEventListener('click', e => {
    if (e.target.id === 'ticketModal') closeTicketModal();
});
</script>
@endsection
