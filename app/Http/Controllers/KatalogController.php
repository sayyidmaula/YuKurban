<?php

namespace App\Http\Controllers;

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use App\Models\Animal;
use App\Models\PriceTier;
use App\Models\Region;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isPenabung = $user && $user->isPenabung();

        $query = Animal::with(['region', 'photos', 'peternakProfile'])
            ->tayang();

        // Filter by animal type
        if ($request->filled('type') && in_array($request->type, ['kambing', 'domba', 'sapi'], true)) {
            $query->where('type', $request->type);
        }

        // Filter by region
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        // Filter by price tier
        if ($request->filled('tier_id')) {
            $tier = PriceTier::find($request->tier_id);
            if ($tier) {
                $query->whereBetween('price', [$tier->min_price, $tier->max_price]);
            }
        }

        // Sorting
        $sort = $request->get('sort', 'price_asc');
        match ($sort) {
            'price_desc' => $query->orderBy('price', 'desc'),
            'weight_desc' => $query->orderBy('weight_kg', 'desc'),
            default => $query->orderBy('price', 'asc'),
        };

        $animals = $query->paginate(12)->withQueryString()->through(function ($animal) {
            // BR-10: Exclude personal user name, phone, email
            return [
                'id' => $animal->id,
                'type' => $animal->type->value,
                'type_label' => $animal->type->label(),
                'title' => $animal->title,
                'age_months' => $animal->age_months,
                'weight_kg' => $animal->weight_kg,
                'price' => $animal->price,
                'region' => $animal->region->full_name,
                'farm_name' => $animal->peternakProfile->farm_name,
                'thumbnail' => $animal->photos->first()?->path,
                'is_available' => $animal->isAvailable(),
            ];
        });

        $regions = Region::where('is_active', true)->orderBy('city')->get();
        $tiers = PriceTier::orderBy('sort_order')->get();

        // Check purchase windows from settings
        $windowSembelih = Setting::get('purchase_window_sembelih_tim', ['open_at' => null, 'close_at' => null]);
        $windowKirim = Setting::get('purchase_window_kirim_hidup', ['open_at' => null, 'close_at' => null]);

        $activeSavingsPlans = $isPenabung
            ? $user->savingsPlans()->where('status', 'menabung')->get(['id', 'name', 'balance_cached'])
            : [];

        return Inertia::render('Public/Katalog/Index', [
            'animals' => $animals,
            'regions' => $regions,
            'tiers' => $tiers,
            'filters' => $request->only(['type', 'region_id', 'tier_id', 'sort']),
            'isPenabung' => $isPenabung,
            'activeSavingsPlans' => $activeSavingsPlans,
            'purchaseWindows' => [
                'sembelih_tim' => $windowSembelih,
                'kirim_hidup' => $windowKirim,
            ],
        ]);
    }

    public function show(Request $request, Animal $animal): Response
    {
        // Must be tayang or user is the owner/admin
        $user = $request->user();
        $isOwner = $user && $user->peternakProfile && $animal->peternak_profile_id === $user->peternakProfile->id;
        $isAdmin = $user && $user->isAdmin();

        if ($animal->status !== AnimalStatus::TAYANG && ! $isOwner && ! $isAdmin) {
            abort(404, 'Hewan kurban tidak ditemukan atau belum tayang.');
        }

        $animal->load(['region', 'media', 'peternakProfile']);

        // BR-10: Exclude personal user name, phone, email of peternak
        $animalData = [
            'id' => $animal->id,
            'type' => $animal->type->value,
            'type_label' => $animal->type->label(),
            'title' => $animal->title,
            'age_months' => $animal->age_months,
            'weight_kg' => $animal->weight_kg,
            'price' => $animal->price,
            'health_notes' => $animal->health_notes,
            'region' => $animal->region->full_name,
            'region_id' => $animal->region_id,
            'farm_name' => $animal->peternakProfile->farm_name,
            'status' => $animal->status->value,
            'is_available' => $animal->isAvailable(),
            'media' => $animal->media->map(fn ($m) => [
                'id' => $m->id,
                'type' => $m->type,
                'path' => $m->path,
                'caption' => $m->caption,
            ]),
        ];

        $isPenabung = $user && $user->isPenabung();
        $savingsPlans = $isPenabung
            ? $user->savingsPlans()->where('status', 'menabung')->get()
            : [];

        return Inertia::render('Public/Katalog/Show', [
            'animal' => $animalData,
            'isPenabung' => $isPenabung,
            'savingsPlans' => $savingsPlans,
        ]);
    }
}

