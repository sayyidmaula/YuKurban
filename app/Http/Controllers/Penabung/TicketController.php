<?php

namespace App\Http\Controllers\Penabung;

use App\Enums\TicketType;
use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Order;
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
            ->with(['order', 'animal'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Penabung/Ticket/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function create(Request $request): Response
    {
        $orderId = $request->query('order_id');
        $animalId = $request->query('animal_id');
        $type = $request->query('type', 'pertanyaan_prabeli');

        $selectedOrder = $orderId ? Order::where('id', $orderId)->where('user_id', $request->user()->id)->first() : null;
        $selectedAnimal = $animalId ? Animal::find($animalId) : null;

        $orders = Order::where('user_id', $request->user()->id)->latest()->get();

        return Inertia::render('Penabung/Ticket/Create', [
            'initialType' => $type,
            'selectedOrder' => $selectedOrder,
            'selectedAnimal' => $selectedAnimal,
            'orders' => $orders,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:pertanyaan_prabeli,laporan_masalah'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'animal_id' => ['nullable', 'exists:animals,id'],
            'category' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
        ]);

        try {
            if ($validated['type'] === 'laporan_masalah') {
                $order = Order::where('id', $validated['order_id'])
                    ->where('user_id', $request->user()->id)
                    ->firstOrFail();

                $ticket = $this->ticketService->createLaporanMasalahTicket(
                    user: $request->user(),
                    order: $order,
                    subject: $validated['subject'],
                    body: $validated['body'],
                    category: $validated['category']
                );
            } else {
                $animal = Animal::findOrFail($validated['animal_id']);

                $ticket = $this->ticketService->createPrabeliTicket(
                    user: $request->user(),
                    animal: $animal,
                    subject: $validated['subject'],
                    body: $validated['body'],
                    category: $validated['category']
                );
            }

            return redirect()->route('tiket.show', $ticket->id)
                ->with('success', 'Tiket bantuan berhasil dibuka. Tim kami akan segera menanggapi.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, Ticket $ticket): Response
    {
        if ($ticket->creator_id !== $request->user()->id) {
            abort(403);
        }

        $ticket->load(['replies.user', 'order', 'animal']);

        return Inertia::render('Penabung/Ticket/Show', [
            'ticket' => $ticket,
        ]);
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
