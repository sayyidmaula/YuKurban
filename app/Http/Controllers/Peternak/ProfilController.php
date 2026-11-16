<?php

namespace App\Http\Controllers\Peternak;

use App\Http\Controllers\Controller;
use App\Models\PeternakProfile;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfilController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $profile = $user->peternakProfile?->load(['region']);
        $regions = Region::where('is_active', true)->orderBy('province')->orderBy('city')->get();

        return Inertia::render('Peternak/Profil', [
            'profile' => $profile,
            'regions' => $regions,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'farm_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'region_id' => ['required', 'exists:regions,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'farm_photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $photoPath = $user->peternakProfile?->farm_photo_path;
        if ($request->hasFile('farm_photo')) {
            $photoPath = $request->file('farm_photo')->store('farms', 'public');
        }

        PeternakProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'farm_name' => $validated['farm_name'],
                'address' => $validated['address'],
                'region_id' => $validated['region_id'],
                'description' => $validated['description'] ?? null,
                'farm_photo_path' => $photoPath,
                'status' => 'menunggu', // always submits for admin review
            ]
        );

        return back()->with('success', 'Profil peternak berhasil disimpan dan diajukan untuk verifikasi.');
    }
}

