<?php

namespace App\Http\Controllers\Peternak;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $profile = $request->user()->peternakProfile;

        if (! $profile) {
            return Inertia::render('Peternak/Order/Index', [
                'orders' => [],
            ]);
        }

        // BR-10: Strictly omit user contact & recipient address! Only order_no, animal, method, status, scheduled date.
        $orders = Order::whereHas('animal', fn ($q) => $q->where('peternak_profile_id', $profile->id))
            ->whereNotIn('status', ['direservasi', 'kedaluwarsa'])
            ->with(['animal'])
            ->select([
                'id',
                'order_no',
                'animal_id',
                'method',
                'status',
                'animal_price',
                'peternak_ready_at',
                'scheduled_at',
                'slaughtered_at',
                'delivered_at',
                'confirmed_at',
                'created_at',
            ])
            ->latest()
            ->paginate(15);

        return Inertia::render('Peternak/Order/Index', [
            'orders' => $orders,
        ]);
    }

    public function payoutIndex(Request $request): Response
    {
        $profile = $request->user()->peternakProfile;

        if (! $profile) {
            return Inertia::render('Peternak/Payout/Index', [
                'payouts' => [],
                'summary' => ['total_tertahan' => 0, 'total_dibayarkan' => 0],
            ]);
        }

        $payouts = Payout::where('peternak_profile_id', $profile->id)
            ->with(['order.animal'])
            ->latest()
            ->paginate(15);

        $totalTertahan = (int) Payout::where('peternak_profile_id', $profile->id)
            ->where('status', 'tertahan')
            ->sum('amount');

        $totalDibayarkan = (int) Payout::where('peternak_profile_id', $profile->id)
            ->where('status', 'dibayarkan')
            ->sum('amount');

        return Inertia::render('Peternak/Payout/Index', [
            'payouts' => $payouts,
            'summary' => [
                'total_tertahan' => $totalTertahan,
                'total_dibayarkan' => $totalDibayarkan,
            ],
        ]);
    }
}

