<?php

namespace App\Services;

use App\Enums\AnimalStatus;
use App\Enums\LedgerType;
use App\Enums\OrderMethod;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Models\Animal;
use App\Models\FeeRule;
use App\Models\Order;
use App\Models\OrderEvidence;
use App\Models\OrderNote;
use App\Models\OrderStatusLog;
use App\Models\Payout;
use App\Models\Region;
use App\Models\SavingsPlan;
use App\Models\ServiceZone;
use App\Models\Setting;
use App\Models\SlaughterQuota;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected LedgerService $ledgerService
    ) {}

    /**
     * Check if purchase window is open for a given method (BR-05).
     */
    public function isPurchaseWindowOpen(OrderMethod $method): bool
    {
        $settingKey = $method === OrderMethod::SEMBELIH_TIM
            ? 'purchase_window_sembelih_tim'
            : 'purchase_window_kirim_hidup';

        $window = Setting::get($settingKey);

        if (! $window || empty($window['open_at']) || empty($window['close_at'])) {
            return false;
        }

        $now = now();

        return $now->between(Carbon::parse($window['open_at']), Carbon::parse($window['close_at']));
    }

    /**
     * Calculate cost breakdown for order (BR-07, BR-09).
     */
    public function calculateCosts(Animal $animal, OrderMethod $method, Region $region): array
    {
        // BR-07: Validate zone support
        $zone = ServiceZone::where('region_id', $region->id)->where('is_active', true)->first();

        if (! $zone) {
            throw new Exception("Wilayah {$region->city} belum termasuk dalam zona layanan kami.");
        }

        if ($method === OrderMethod::SEMBELIH_TIM && ! $zone->supports_sembelih_tim) {
            throw new Exception("Metode Sembelih Tim tidak didukung untuk wilayah {$region->city}.");
        }

        if ($method === OrderMethod::KIRIM_HIDUP && ! $zone->supports_kirim_hidup) {
            throw new Exception("Metode Kirim Hewan Hidup tidak didukung untuk wilayah {$region->city}.");
        }

        // BR-09: Fee rules per animal type
        $feeRule = FeeRule::getForAnimalType($animal->type);
        if (! $feeRule) {
            throw new Exception("Aturan biaya untuk jenis {$animal->type->value} belum dikonfigurasi.");
        }

        $animalPrice = (int) $animal->price;
        $commission = (int) $feeRule->commission_peternak;

        if ($method === OrderMethod::SEMBELIH_TIM) {
            $feeSlaughter = (int) $feeRule->fee_slaughter;
            $feeDistribution = (int) $feeRule->fee_distribution;
            $feeDelivery = 0;
            $totalBuyerPays = $animalPrice + $feeSlaughter + $feeDistribution;
        } else {
            $feeSlaughter = 0;
            $feeDistribution = 0;
            $feeDelivery = (int) $zone->delivery_fee;
            $totalBuyerPays = $animalPrice + $feeDelivery;
        }

        $payoutPeternak = max(0, $animalPrice - $commission);

        return [
            'animal_price' => $animalPrice,
            'fee_slaughter' => $feeSlaughter,
            'fee_distribution' => $feeDistribution,
            'fee_delivery' => $feeDelivery,
            'commission' => $commission,
            'total_buyer_pays' => $totalBuyerPays,
            'payout_peternak' => $payoutPeternak,
        ];
    }

    /**
     * Reserve an animal and create a pending reservation order (BR-05, BR-06, BR-07, BR-09).
     */
    public function reserveOrder(
        User $user,
        SavingsPlan $savingsPlan,
        Animal $animal,
        OrderMethod $method,
        Region $region
    ): Order {
        if (! $this->isPurchaseWindowOpen($method)) {
            throw new Exception('Jendela pembelian untuk metode ini sedang ditutup.');
        }

        if ($savingsPlan->user_id !== $user->id) {
            throw new Exception('Rencana tabungan tidak valid.');
        }

        $costs = $this->calculateCosts($animal, $method, $region);

        if ($savingsPlan->balance_cached < $costs['total_buyer_pays']) {
            throw new Exception('Saldo tabungan Anda tidak mencukupi untuk melakukan pembelian ini.');
        }

        $reservationHours = (int) (Setting::get('reservation_hours', 24));
        $reservedUntil = now()->addHours($reservationHours);

        return DB::transaction(function () use ($user, $savingsPlan, $animal, $method, $region, $costs, $reservedUntil) {
            // Lock animal for update
            $lockedAnimal = Animal::where('id', $animal->id)->lockForUpdate()->firstOrFail();

            if ($lockedAnimal->status !== AnimalStatus::TAYANG) {
                throw new Exception('Hewan kurban ini sudah tidak berstatus tayang atau sedang dipesan pengguna lain.');
            }

            // Update animal reservation
            $lockedAnimal->update([
                'status' => AnimalStatus::DIPESAN,
                'reserved_by' => $user->id,
                'reserved_until' => $reservedUntil,
            ]);

            // Generate unique order number
            $orderNo = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

            $order = Order::create([
                'order_no' => $orderNo,
                'user_id' => $user->id,
                'savings_plan_id' => $savingsPlan->id,
                'animal_id' => $lockedAnimal->id,
                'region_id' => $region->id,
                'method' => $method,
                'status' => OrderStatus::DIRESERVASI,
                'animal_price' => $costs['animal_price'],
                'fee_slaughter' => $costs['fee_slaughter'],
                'fee_distribution' => $costs['fee_distribution'],
                'fee_delivery' => $costs['fee_delivery'],
                'commission' => $costs['commission'],
                'total_buyer_pays' => $costs['total_buyer_pays'],
                'reserved_until' => $reservedUntil,
            ]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => OrderStatus::DIRESERVASI->value,
                'actor_id' => $user->id,
                'note' => "Reservasi hewan dibuat berlaku sampai {$reservedUntil->format('d M Y H:i')}",
            ]);

            return $order;
        });
    }

    /**
     * Confirm an order, deducting ledger balance and putting payout on hold (BR-01, BR-03, BR-12, BR-13).
     */
    public function confirmOrder(Order $order, array $confirmationData = []): Order
    {
        return DB::transaction(function () use ($order, $confirmationData) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::DIRESERVASI) {
                throw new Exception('Hanya pesanan berstatus direservasi yang dapat dikonfirmasi.');
            }

            if ($lockedOrder->reserved_until && $lockedOrder->reserved_until->isPast()) {
                $this->expireReservation($lockedOrder);
                throw new Exception('Batas waktu reservasi telah kedaluwarsa.');
            }

            // BR-12: Wakalah or recipient validation
            $wakalahAgreedAt = null;
            $recipientName = null;
            $recipientPhone = null;
            $recipientAddress = null;
            $distributionNote = $confirmationData['distribution_note'] ?? null;

            if ($lockedOrder->isSembelihTim()) {
                if (empty($confirmationData['wakalah_agreed'])) {
                    throw new Exception('Anda wajib menyetujui akad wakalah (pemberian kuasa) penyembelihan dan penyaluran.');
                }
                $wakalahAgreedAt = now();
            } else {
                if (empty($confirmationData['recipient_name']) || empty($confirmationData['recipient_phone']) || empty($confirmationData['recipient_address'])) {
                    throw new Exception('Nama, telepon, dan alamat penerima wajib diisi lengkap untuk pengiriman hewan.');
                }
                $recipientName = $confirmationData['recipient_name'];
                $recipientPhone = $confirmationData['recipient_phone'];
                $recipientAddress = $confirmationData['recipient_address'];
            }

            // Deduct ledger via single write gateway LedgerService (BR-01, BR-03)
            $plan = SavingsPlan::where('id', $lockedOrder->savings_plan_id)->lockForUpdate()->firstOrFail();

            $this->ledgerService->record(
                plan: $plan,
                type: LedgerType::PEMBELIAN,
                amount: $lockedOrder->total_buyer_pays,
                description: "Pembelian hewan kurban pesanan {$lockedOrder->order_no}",
                actor: $lockedOrder->user,
                refType: Order::class,
                refId: $lockedOrder->id,
                explicitDirection: 'keluar'
            );

            // Update order
            $lockedOrder->update([
                'status' => OrderStatus::DIKONFIRMASI,
                'confirmed_at' => now(),
                'wakalah_agreed_at' => $wakalahAgreedAt,
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'recipient_address' => $recipientAddress,
                'distribution_note' => $distributionNote,
            ]);

            // Create Escrow Payout (BR-13)
            $animal = $lockedOrder->animal;
            $payoutAmount = max(0, $lockedOrder->animal_price - $lockedOrder->commission);

            Payout::create([
                'order_id' => $lockedOrder->id,
                'peternak_profile_id' => $animal->peternak_profile_id,
                'amount' => $payoutAmount,
                'commission' => $lockedOrder->commission,
                'status' => PayoutStatus::TERTAHAN,
            ]);

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => OrderStatus::DIRESERVASI->value,
                'to_status' => OrderStatus::DIKONFIRMASI->value,
                'actor_id' => $lockedOrder->user_id,
                'note' => 'Pesanan dikonfirmasi dan pembayaran diproses dari tabungan.',
            ]);

            return $lockedOrder;
        });
    }

    /**
     * Expire a pending reservation and release the animal back to catalog (BR-06).
     */
    public function expireReservation(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::DIRESERVASI) {
                return;
            }

            $lockedOrder->update([
                'status' => OrderStatus::KEDALUWARSA,
            ]);

            $animal = Animal::where('id', $lockedOrder->animal_id)->lockForUpdate()->first();
            if ($animal && $animal->status === AnimalStatus::DIPESAN && $animal->reserved_by == $lockedOrder->user_id) {
                $animal->update([
                    'status' => AnimalStatus::TAYANG,
                    'reserved_by' => null,
                    'reserved_until' => null,
                ]);
            }

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => OrderStatus::DIRESERVASI->value,
                'to_status' => OrderStatus::KEDALUWARSA->value,
                'actor_id' => null,
                'note' => 'Reservasi kedaluwarsa, hewan dikembalikan ke katalog.',
            ]);
        });
    }

    /**
     * Cancel confirmed order before scheduling, full refund to savings plan (BR-14).
     */
    public function cancelBeforeSchedule(Order $order, User $actor, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $reason) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::DIKONFIRMASI) {
                throw new Exception('Pesanan hanya dapat dibatalkan mandiri saat masih berstatus dikonfirmasi (sebelum dijadwalkan).');
            }

            // Refund full amount to savings plan ledger (BR-14)
            $plan = SavingsPlan::where('id', $lockedOrder->savings_plan_id)->lockForUpdate()->firstOrFail();

            $this->ledgerService->record(
                plan: $plan,
                type: LedgerType::PENGEMBALIAN_DANA,
                amount: $lockedOrder->total_buyer_pays,
                description: "Pengembalian dana pembatalan pesanan {$lockedOrder->order_no}",
                actor: $actor,
                refType: Order::class,
                refId: $lockedOrder->id,
                explicitDirection: 'masuk'
            );

            // Cancel payout
            if ($lockedOrder->payout) {
                $lockedOrder->payout->update([
                    'status' => PayoutStatus::DIBATALKAN,
                ]);
            }

            // Release animal back to tayang
            $animal = Animal::where('id', $lockedOrder->animal_id)->lockForUpdate()->first();
            if ($animal) {
                $animal->update([
                    'status' => AnimalStatus::TAYANG,
                    'reserved_by' => null,
                    'reserved_until' => null,
                ]);
            }

            $lockedOrder->update([
                'status' => OrderStatus::DIBATALKAN,
            ]);

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => OrderStatus::DIKONFIRMASI->value,
                'to_status' => OrderStatus::DIBATALKAN->value,
                'actor_id' => $actor->id,
                'note' => $reason ? "Pesanan dibatalkan: {$reason}" : 'Pesanan dibatalkan penabung sebelum jadwal.',
            ]);

            return $lockedOrder;
        });
    }

    /**
     * Sembelih Tim Alur: Jadwalkan penyembelihan (BR-08).
     */
    public function scheduleSembelihTim(Order $order, Carbon|string $date, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $date, $actor, $note) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::DIKONFIRMASI) {
                throw new Exception('Pesanan hanya dapat dijadwalkan saat berstatus dikonfirmasi.');
            }

            if (! $lockedOrder->isSembelihTim()) {
                throw new Exception('Pesanan ini bukan metode sembelih tim.');
            }

            $scheduledDate = Carbon::parse($date)->startOfDay();
            $remainingQuota = SlaughterQuota::getRemainingQuota($scheduledDate->format('Y-m-d'));

            if ($remainingQuota <= 0) {
                throw new Exception("Kuota sembelih pada tanggal {$scheduledDate->format('d M Y')} telah penuh.");
            }

            $lockedOrder->update([
                'status' => OrderStatus::DIJADWALKAN,
                'scheduled_at' => $scheduledDate,
            ]);

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => OrderStatus::DIKONFIRMASI->value,
                'to_status' => OrderStatus::DIJADWALKAN->value,
                'actor_id' => $actor->id,
                'note' => $note ?? "Penyembelihan dijadwalkan pada tanggal {$scheduledDate->format('d M Y')}.",
            ]);

            return $lockedOrder;
        });
    }

    /**
     * Sembelih Tim Alur: Tandai hewan telah disembelih.
     */
    public function markSlaughtered(Order $order, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $note) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::DIJADWALKAN) {
                throw new Exception('Hanya pesanan yang telah dijadwalkan yang dapat ditandai disembelih.');
            }

            if (! $lockedOrder->isSembelihTim()) {
                throw new Exception('Pesanan ini bukan metode sembelih tim.');
            }

            $lockedOrder->update([
                'status' => OrderStatus::DISEMBELIH,
                'slaughtered_at' => now(),
            ]);

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => OrderStatus::DIJADWALKAN->value,
                'to_status' => OrderStatus::DISEMBELIH->value,
                'actor_id' => $actor->id,
                'note' => $note ?? 'Penyembelihan hewan kurban telah selesai dilaksanakan.',
            ]);

            return $lockedOrder;
        });
    }

    /**
     * Sembelih Tim Alur: Tandai daging telah disalurkan.
     */
    public function markDistributed(Order $order, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $note) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::DISEMBELIH) {
                throw new Exception('Hanya pesanan yang telah disembelih yang dapat ditandai disalurkan.');
            }

            if (! $lockedOrder->isSembelihTim()) {
                throw new Exception('Pesanan ini bukan metode sembelih tim.');
            }

            $autoConfirmHours = (int) Setting::get('auto_confirm_hours', 48);

            $lockedOrder->update([
                'status' => OrderStatus::DISALURKAN,
                'delivered_at' => now(),
                'auto_confirm_at' => now()->addHours($autoConfirmHours),
            ]);

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => OrderStatus::DISEMBELIH->value,
                'to_status' => OrderStatus::DISALURKAN->value,
                'actor_id' => $actor->id,
                'note' => $note ?? 'Daging kurban telah berhasil didistribusikan kepada penerima manfaat.',
            ]);

            return $lockedOrder;
        });
    }

    /**
     * Kirim Hidup Alur: Jadwalkan pengiriman hewan hidup.
     */
    public function scheduleKirimHidup(Order $order, Carbon|string $date, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $date, $actor, $note) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::DIKONFIRMASI) {
                throw new Exception('Pesanan hanya dapat dijadwalkan saat berstatus dikonfirmasi.');
            }

            if (! $lockedOrder->isKirimHidup()) {
                throw new Exception('Pesanan ini bukan metode kirim hidup.');
            }

            $scheduledDate = Carbon::parse($date);

            $lockedOrder->update([
                'status' => OrderStatus::DIJADWALKAN_KIRIM,
                'scheduled_at' => $scheduledDate,
            ]);

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => OrderStatus::DIKONFIRMASI->value,
                'to_status' => OrderStatus::DIJADWALKAN_KIRIM->value,
                'actor_id' => $actor->id,
                'note' => $note ?? "Pengiriman hewan dijadwalkan pada tanggal {$scheduledDate->format('d M Y H:i')}.",
            ]);

            return $lockedOrder;
        });
    }

    /**
     * Kirim Hidup Alur: Tandai hewan dalam perjalanan pengiriman.
     */
    public function markInTransit(Order $order, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $note) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::DIJADWALKAN_KIRIM) {
                throw new Exception('Hanya pesanan yang telah dijadwalkan kirim yang dapat ditandai dalam perjalanan.');
            }

            if (! $lockedOrder->isKirimHidup()) {
                throw new Exception('Pesanan ini bukan metode kirim hidup.');
            }

            $lockedOrder->update([
                'status' => OrderStatus::DALAM_PERJALANAN,
            ]);

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => OrderStatus::DIJADWALKAN_KIRIM->value,
                'to_status' => OrderStatus::DALAM_PERJALANAN->value,
                'actor_id' => $actor->id,
                'note' => $note ?? 'Hewan kurban dalam perjalanan menuju alamat penerima.',
            ]);

            return $lockedOrder;
        });
    }

    /**
     * Kirim Hidup Alur: Tandai hewan telah diterima pemesan.
     */
    public function markDelivered(Order $order, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $note) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::DALAM_PERJALANAN) {
                throw new Exception('Hanya pesanan dalam perjalanan yang dapat ditandai diterima.');
            }

            if (! $lockedOrder->isKirimHidup()) {
                throw new Exception('Pesanan ini bukan metode kirim hidup.');
            }

            $autoConfirmHours = (int) Setting::get('auto_confirm_hours', 48);

            $lockedOrder->update([
                'status' => OrderStatus::DITERIMA,
                'delivered_at' => now(),
                'auto_confirm_at' => now()->addHours($autoConfirmHours),
            ]);

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => OrderStatus::DALAM_PERJALANAN->value,
                'to_status' => OrderStatus::DITERIMA->value,
                'actor_id' => $actor->id,
                'note' => $note ?? 'Hewan kurban telah berhasil diterima di alamat tujuan.',
            ]);

            return $lockedOrder;
        });
    }

    /**
     * Konfirmasi Selesai Pesanan & Rilis Payout ke Peternak (BR-13).
     */
    public function completeOrder(Order $order, ?User $actor = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $note) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedOrder->status, [OrderStatus::DISALURKAN, OrderStatus::DITERIMA], true)) {
                throw new Exception('Hanya pesanan yang sudah disalurkan atau diterima yang dapat diselesaikan.');
            }

            $fromStatus = $lockedOrder->status;

            $lockedOrder->update([
                'status' => OrderStatus::SELESAI,
            ]);

            // Release Escrow Payout (BR-13)
            if ($lockedOrder->payout && $lockedOrder->payout->status === PayoutStatus::TERTAHAN) {
                $lockedOrder->payout->update([
                    'status' => PayoutStatus::DIBAYARKAN,
                    'released_at' => now(),
                ]);
            }

            // Animal lifecycle: mark as TERJUAL
            $animal = Animal::where('id', $lockedOrder->animal_id)->lockForUpdate()->first();
            if ($animal) {
                $animal->update([
                    'status' => AnimalStatus::TERJUAL,
                ]);
            }

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => $fromStatus->value,
                'to_status' => OrderStatus::SELESAI->value,
                'actor_id' => $actor?->id,
                'note' => $note ?? 'Pesanan telah selesai. Dana payout diteruskan ke peternak.',
            ]);

            return $lockedOrder;
        });
    }

    /**
     * Peternak: Tandai hewan kurban siap diambil / dikirim.
     */
    public function markPeternakReady(Order $order, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $note) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            $lockedOrder->update([
                'peternak_ready_at' => now(),
            ]);

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => $lockedOrder->status->value,
                'to_status' => $lockedOrder->status->value,
                'actor_id' => $actor->id,
                'note' => $note ?? 'Peternak mengonfirmasi hewan kurban telah siap di kandang.',
            ]);

            return $lockedOrder;
        });
    }

    /**
     * Upload / record evidence.
     */
    public function addEvidence(Order $order, string $stage, string $type, string $path, ?User $actor = null): OrderEvidence
    {
        return OrderEvidence::create([
            'order_id' => $order->id,
            'stage' => $stage,
            'type' => $type,
            'path' => $path,
            'uploaded_by' => $actor?->id,
        ]);
    }

    /**
     * Add internal order note (for admin and peternak).
     */
    public function addNote(Order $order, User $user, string $body): OrderNote
    {
        return OrderNote::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'body' => $body,
        ]);
    }

    /**
     * Auto-confirm eligible orders past auto_confirm_at.
     */
    public function autoConfirmOrders(): int
    {
        $eligibleOrders = Order::whereIn('status', [OrderStatus::DISALURKAN, OrderStatus::DITERIMA])
            ->whereNotNull('auto_confirm_at')
            ->where('auto_confirm_at', '<=', now())
            ->where('is_disputed', false)
            ->get();

        $count = 0;
        foreach ($eligibleOrders as $order) {
            $this->completeOrder($order, null, 'Otomatis selesai setelah batas waktu konfirmasi terlampaui.');
            $count++;
        }

        return $count;
    }
}

