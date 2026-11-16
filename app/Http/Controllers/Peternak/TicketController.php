<?php

namespace App\Http\Controllers\Peternak;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService $ticketService
    ) {}

    public function index(Request $request): Response
    {
        $tickets = Ticket::where('creator_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('Peternak/Bantuan', [
            'tickets' => $tickets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
        ]);

        try {
            $ticket = $this->ticketService->createBantuanPeternakTicket(
                user: $request->user(),
                subject: $validated['subject'],
                body: $validated['body'],
                category: $validated['category']
            );

            return back()->with('success', 'Tiket permohonan bantuan berhasil dikirim.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->creator_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $this->ticketService->replyTicket($ticket, $request->user(), $validated['body']);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }
}
