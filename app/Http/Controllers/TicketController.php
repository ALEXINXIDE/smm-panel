<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('support.index', compact('tickets'));
    }

    public function create()
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->latest()
            ->take(10)
            ->get();

        return view('support.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'category' => 'required|in:order,payment,technical,other',
            'order_id' => 'nullable|integer|exists:orders,id',
        ]);

        try {
            $ticket = Ticket::create([
                'user_id' => Auth::id(),
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'category' => $validated['category'],
                'order_id' => $validated['order_id'] ?? null,
                'status' => 'open',
            ]);

            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $validated['message'],
                'is_admin' => false,
            ]);

            Log::info('Support ticket created', [
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'category' => $validated['category'],
            ]);

            return redirect()->route('tickets.show', $ticket->id)
                ->with('success', 'Ticket #' . $ticket->id . ' opened. We\'ll respond within 2-4 hours.');
        } catch (\Exception $e) {
            Log::error('Ticket creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create ticket. Please try again.']);
        }
    }

    public function show(Ticket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);
        $ticket->load('messages.user');
        $tickets = Ticket::where('user_id', Auth::id())
            ->latest()
            ->take(20)
            ->get(['id', 'subject', 'status', 'created_at']);

        return view('support.index', compact('ticket', 'tickets'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'message' => 'required|string|max:5000'
        ]);

        try {
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $validated['message'],
                'is_admin' => false,
            ]);

            $ticket->update(['status' => 'open']);

            Log::info('Ticket reply added', [
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
            ]);

            return back()->with('success', 'Reply sent.');
        } catch (\Exception $e) {
            Log::error('Ticket reply failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to send reply. Please try again.']);
        }
    }
}
