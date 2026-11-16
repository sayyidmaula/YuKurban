<?php

namespace App\Services;

use App\Enums\AnimalStatus;
use App\Enums\LedgerType;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Enums\TicketDecision;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Animal;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\SavingsPlan;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct(
        protected LedgerService $ledgerService
    ) {}

    /**
     * Buat tiket pertanyaan pra-beli dari halaman hewan kurban.
     */
    public function createPrabeliTicket(
        User $user,
        Animal $animal,
        string $subject,
        string $body,
        string $category = 'Pertanyaan Pra-Beli'
    ): Ticket {
        return DB::transaction(function () use ($user, $animal, $subject, $body, $category) {
            $ticket = Ticket::create([
                'type' => TicketType::PERTANYAAN_PRABELI,
                'creator_id' => $user->id,
                'animal_id' => $animal->id,
                'order_id' => null,
                'category' => $category,
                'subject' => $subject,
                'status' => TicketStatus::DIBUKA,
            ]);

            TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => $body,
                'is_faq_public' => false,
            ]);

            return $ticket;
        });
    }

    /**
     * Buat tiket laporan masalah terkait pesanan (BR-15).
     */
    public function createLaporanMasalahTicket(
        User $user,
        Order $order,
        string $subject,
        string $body,
        string $category = 'Kualitas Pelaksanaan'
    ): Ticket {
        // Must belong to user
        if ($order->user_id !== $user->id) {
            throw new Exception('Anda hanya dapat membuat laporan masalah untuk pesanan milik Anda sendiri.');
        }

        // BR-15: Batas lapor masalah
        if ($order->delivered_at) {
            $deadlineHours = (int) Setting::get('report_deadline_hours', 48);
            $deadline = \Carbon\Carbon::parse($order->delivered_at)->addHours($deadlineHours);
            if (now()->greaterThan($deadline)) {
                throw new Exception("Batas waktu pelaporan masalah ({$deadlineHours} jam setelah penyaluran/penerimaan) telah berakhir.");
            }
        }

        return DB::transaction(function () use ($user, $order, $subject, $body, $category) {
            $ticket = Ticket::create([
                'type' => TicketType::LAPORAN_MASALAH,
                'creator_id' => $user->id,
                'order_id' => $order->id,
                'animal_id' => $order->animal_id,
                'category' => $category,
                'subject' => $subject,
                'status' => TicketStatus::DIBUKA,
            ]);

            TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => $body,
                'is_faq_public' => false,
            ]);

            // Mark order as disputed to pause auto-completion
            $order->update(['is_disputed' => true]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $order->status->value,
                'to_status' => $order->status->value,
                'actor_id' => $user->id,
                'note' => "Laporan masalah pesanan dibuka: {$subject} (Tiket #{$ticket->id})",
            ]);

            return $ticket;
        });
    }

    /**
     * Buat tiket bantuan mitra peternak.
     */
    public function createBantuanPeternakTicket(
        User $user,
        string $subject,
        string $body,
        string $category = 'Kemitraan & Operasional'
    ): Ticket {
        return DB::transaction(function () use ($user, $subject, $body, $category) {
            $ticket = Ticket::create([
                'type' => TicketType::BANTUAN_PETERNAK,
                'creator_id' => $user->id,
                'order_id' => null,
                'animal_id' => null,
                'category' => $category,
                'subject' => $subject,
                'status' => TicketStatus::DIBUKA,
            ]);

            TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => $body,
                'is_faq_public' => false,
            ]);

            return $ticket;
        });
    }

    /**
     * Balas tiket diskusi.
     */
    public function replyTicket(Ticket $ticket, User $user, string $body, bool $isFaqPublic = false): TicketReply
    {
        return DB::transaction(function () use ($ticket, $user, $body, $isFaqPublic) {
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => $body,
                'is_faq_public' => $isFaqPublic,
            ]);

            // If admin replies and status was dibuka, update status to ditanggapi
            if ($user->isAdmin() && $ticket->status === TicketStatus::DIBUKA) {
                $ticket->update(['status' => TicketStatus::DITANGGAPI]);
            }

            return $reply;
        });
    }

    /**
     * Keputusan admin atas tiket laporan masalah.
     * Keputusan: ganti_hewan, dana_kembali (refund penuh), atau ditolak.
     */
    public function decideLaporanMasalah(
        Ticket $ticket,
        TicketDecision $decision,
        string $decisionNote,
        User $admin
    ): Ticket {
        if ($ticket->type !== TicketType::LAPORAN_MASALAH) {
            throw new Exception('Keputusan penyelesaian sengketa hanya berlaku untuk tiket laporan masalah.');
        }

        return DB::transaction(function () use ($ticket, $decision, $decisionNote, $admin) {
            $ticket->update([
                'status' => TicketStatus::DIPUTUSKAN,
                'decision' => $decision,
                'decision_note' => $decisionNote,
                'decided_by' => $admin->id,
                'decided_at' => now(),
            ]);

            $order = $ticket->order;

            if ($order) {
                if ($decision === TicketDecision::DANA_KEMBALI) {
                    // BR-01, BR-03: Refund via LedgerService
                    $plan = SavingsPlan::where('id', $order->savings_plan_id)->lockForUpdate()->firstOrFail();

                    $this->ledgerService->record(
                        plan: $plan,
                        type: LedgerType::PENGEMBALIAN_DANA,
                        amount: $order->total_buyer_pays,
                        description: "Pengembalian dana putusan tiket #{$ticket->id} ({$ticket->subject})",
                        actor: $admin,
                        refType: Ticket::class,
                        refId: $ticket->id,
                        explicitDirection: 'masuk'
                    );

                    // Cancel Payout (BR-13)
                    if ($order->payout) {
                        $order->payout->update([
                            'status' => PayoutStatus::DIBATALKAN,
                        ]);
                    }

                    $order->update([
                        'status' => OrderStatus::DIBATALKAN,
                        'is_disputed' => false,
                    ]);

                    OrderStatusLog::create([
                        'order_id' => $order->id,
                        'from_status' => $order->status->value,
                        'to_status' => OrderStatus::DIBATALKAN->value,
                        'actor_id' => $admin->id,
                        'note' => "Pesanan dibatalkan berdasarkan putusan tiket #{$ticket->id}: Dana kembali ke saldo tabungan.",
                    ]);
                } elseif ($decision === TicketDecision::GANTI_HEWAN) {
                    $order->update(['is_disputed' => false]);

                    OrderStatusLog::create([
                        'order_id' => $order->id,
                        'from_status' => $order->status->value,
                        'to_status' => $order->status->value,
                        'actor_id' => $admin->id,
                        'note' => "Putusan tiket #{$ticket->id}: Disetujui pergantian hewan kurban.",
                    ]);
                } else { // DITOLAK
                    $order->update(['is_disputed' => false]);

                    OrderStatusLog::create([
                        'order_id' => $order->id,
                        'from_status' => $order->status->value,
                        'to_status' => $order->status->value,
                        'actor_id' => $admin->id,
                        'note' => "Laporan masalah tiket #{$ticket->id} ditolak: {$decisionNote}",
                    ]);
                }
            }

            return $ticket;
        });
    }

    /**
     * Tutup atau selesaikan tiket.
     */
    public function closeTicket(Ticket $ticket, ?User $actor = null): Ticket
    {
        $ticket->update(['status' => TicketStatus::SELESAI]);

        if ($ticket->order) {
            // Check if there are other open dispute tickets
            $hasOtherDisputes = Ticket::where('order_id', $ticket->order_id)
                ->where('id', '!=', $ticket->id)
                ->whereIn('status', [TicketStatus::DIBUKA, TicketStatus::DITANGGAPI])
                ->exists();

            if (! $hasOtherDisputes) {
                $ticket->order->update(['is_disputed' => false]);
            }
        }

        return $ticket;
    }
}
