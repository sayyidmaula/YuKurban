<?php

namespace App\Http\Controllers\Penabung;

use App\Enums\AnimalStatus;
use App\Enums\OrderMethod;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\FeeRule;
use App\Models\Order;
use App\Models\Region;
use App\Models\SavingsPlan;
use App\Models\ServiceZone;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): Response
    {
        $orders = Order::with(['animal.media', 'region', 'savingsPlan'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('Penabung/Order/Index', [
            'orders' => $orders,
        ]);
    }

    public function create(Request $request, Animal $animal): Response|RedirectResponse
    {
        if ($animal->status !== AnimalStatus::TAYANG) {
            return redirect()->route('katalog.show', $animal->id)
                ->with('error', 'Hewan kurban tidak berstatus tayang atau sedang dipesan.');
        }

        $user = $request->user();
        $plans = SavingsPlan::where('user_id', $user->id)
            ->where('status', 'menabung')
            ->get();

        $regions = Region::where('is_active', true)
            ->whereHas('serviceZone', fn ($q) => $q->where('is_active', true))
            ->with('serviceZone')
            ->get();

        $isSembelihOpen = $this->orderService->isPurchaseWindowOpen(OrderMethod::SEMBELIH_TIM);
        $isKirimOpen = $this->orderService->isPurchaseWindowOpen(OrderMethod::KIRIM_HIDUP);

        $feeRule = FeeRule::getForAnimalType($animal->type);

        return Inertia::render('Penabung/Order/Create', [
            'animal' => [
                'id' => $animal->id,
                'title' => $animal->title,
                'type' => $animal->type->value,
                'type_label' => ucfirst($animal->type->value),
                'price' => (int) $animal->price,
                'age_months' => $animal->age_months,
                'weight_kg' => $animal->weight_kg,
                'farm_name' => $animal->peternakProfile->farm_name,
                'media' => $animal->media,
            ],
            'savingsPlans' => $plans,
            'regions' => $regions,
            'isSembelihOpen' => $isSembelihOpen,
            'isKirimOpen' => $isKirimOpen,
            'feeRule' => $feeRule,
        ]);
    }

    public function reserve(Request $request, Animal $animal): RedirectResponse
    {
        $validated = $request->validate([
            'savings_plan_id' => ['required', 'exists:savings_plans,id'],
            'region_id' => ['required', 'exists:regions,id'],
            'method' => ['required', 'in:sembelih_tim,kirim_hidup'],
        ]);

        $savingsPlan = SavingsPlan::where('id', $validated['savings_plan_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $region = Region::findOrFail($validated['region_id']);
        $method = OrderMethod::from($validated['method']);

        try {
            $order = $this->orderService->reserveOrder(
                user: $request->user(),
                savingsPlan: $savingsPlan,
                animal: $animal,
                method: $method,
                region: $region
            );

            return redirect()->route('pesanan.show', $order->id)
                ->with('success', 'Hewan berhasil direservasi selama 24 jam. Silakan konfirmasi pesanan Anda.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, Order $order): Response
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['animal.media', 'region', 'savingsPlan', 'statusLogs', 'evidences.uploader']);

        return Inertia::render('Penabung/Order/Show', [
            'order' => $order,
        ]);
    }

    public function confirm(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $rules = [];
        if ($order->isSembelihTim()) {
            $rules['wakalah_agreed'] = ['required', 'accepted'];
            $rules['distribution_note'] = ['nullable', 'string', 'max:500'];
        } else {
            $rules['recipient_name'] = ['required', 'string', 'max:100'];
            $rules['recipient_phone'] = ['required', 'string', 'max:20'];
            $rules['recipient_address'] = ['required', 'string', 'max:500'];
        }

        $validated = $request->validate($rules);

        try {
            $this->orderService->confirmOrder($order, $validated);

            return redirect()->route('pesanan.sukses', $order->id);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function sukses(Request $request, Order $order): Response
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['animal', 'region', 'savingsPlan']);

        return Inertia::render('Penabung/Order/Sukses', [
            'order' => $order,
        ]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->orderService->cancelBeforeSchedule($order, $request->user(), 'Dibatalkan oleh penabung.');

            return back()->with('success', 'Pesanan berhasil dibatalkan dan seluruh dana dikembalikan ke tabungan Anda.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->orderService->completeOrder($order, $request->user(), 'Dikonfirmasi selesai oleh pemesan.');

            return back()->with('success', 'Pesanan telah berhasil diselesaikan. Terima kasih atas kepercayaan Anda berkurban bersama YuKurban!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

