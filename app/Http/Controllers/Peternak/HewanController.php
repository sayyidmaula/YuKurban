<?php

namespace App\Http\Controllers\Peternak;

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalMedia;
use App\Models\Region;
use App\Services\AnimalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HewanController extends Controller
{
    public function __construct(
        protected AnimalService $animalService
    ) {}

    public function index(Request $request): Response
    {
        $profile = $request->user()->peternakProfile;

        $animals = $profile
            ? Animal::with(['region', 'photos'])
                ->where('peternak_profile_id', $profile->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn ($animal) => [
                    'id' => $animal->id,
                    'title' => $animal->title,
                    'type' => $animal->type->value,
                    'type_label' => $animal->type->label(),
                    'age_months' => $animal->age_months,
                    'weight_kg' => $animal->weight_kg,
                    'price' => $animal->price,
                    'status' => $animal->status->value,
                    'status_label' => $animal->status->label(),
                    'region' => $animal->region->full_name,
                    'thumbnail' => $animal->photos->first()?->path,
                ])
            : [];

        return Inertia::render('Peternak/Hewan/Index', [
            'animals' => $animals,
            'isVerified' => $profile?->isVerified() ?? false,
            'profileStatus' => $profile?->status ?? 'belum_ada',
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $profile = $request->user()->peternakProfile;
        if (! $profile) {
            return redirect()->route('peternak.profil')
                ->with('info', 'Silakan lengkapi profil peternak Anda terlebih dahulu.');
        }

        $regions = Region::where('is_active', true)->orderBy('province')->orderBy('city')->get();

        return Inertia::render('Peternak/Hewan/Create', [
            'regions' => $regions,
            'minAges' => [
                'kambing' => $this->animalService->getMinAgeMonths('kambing'),
                'domba' => $this->animalService->getMinAgeMonths('domba'),
                'sapi' => $this->animalService->getMinAgeMonths('sapi'),
            ],
            'defaultRegionId' => $profile->region_id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->peternakProfile;
        if (! $profile) {
            abort(403, 'Profil peternak belum dibuat.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AnimalType::class)],
            'age_months' => ['required', 'integer', 'min:1'],
            'weight_kg' => ['required', 'numeric', 'min:10', 'max:2000'],
            'price' => ['required', 'integer', 'min:1000000'],
            'region_id' => ['required', 'exists:regions,id'],
            'health_notes' => ['required', 'string', 'max:2000'],
            'photos' => ['required', 'array', 'min:3'],
            'photos.*' => ['image', 'max:5120'],
            'video' => ['required', 'mimes:mp4,mov,webm', 'max:51200'],
        ], [
            'photos.min' => 'Sesuai BR-16, wajib mengunggah minimal 3 foto hewan kurban dari berbagai sudut.',
            'video.required' => 'Sesuai BR-16, wajib mengunggah minimal 1 video kondisi fisik hewan kurban.',
        ]);

        // Validate age against official settings (BR-16)
        $this->animalService->validateAnimalAge($validated['type'], (int) $validated['age_months']);

        $animal = Animal::create([
            'peternak_profile_id' => $profile->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'age_months' => (int) $validated['age_months'],
            'weight_kg' => $validated['weight_kg'],
            'price' => (int) $validated['price'],
            'region_id' => $validated['region_id'],
            'health_notes' => $validated['health_notes'],
            'status' => AnimalStatus::MENUNGGU_REVIEW,
        ]);

        // Store photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $idx => $photo) {
                $path = $photo->store('animals/photos', 'public');
                AnimalMedia::create([
                    'animal_id' => $animal->id,
                    'type' => 'foto',
                    'path' => $path,
                    'sort_order' => $idx + 1,
                ]);
            }
        }

        // Store video
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('animals/videos', 'public');
            AnimalMedia::create([
                'animal_id' => $animal->id,
                'type' => 'video',
                'path' => $videoPath,
                'sort_order' => 10,
            ]);
        }

        return redirect()->route('peternak.hewan.index')
            ->with('success', 'Data hewan berhasil ditambahkan dan diajukan untuk review kurasi admin.');
    }

    public function edit(Request $request, Animal $animal): Response
    {
        $profile = $request->user()->peternakProfile;
        if (! $profile || $animal->peternak_profile_id !== $profile->id) {
            abort(403);
        }

        $regions = Region::where('is_active', true)->orderBy('province')->orderBy('city')->get();
        $animal->load(['media', 'region']);

        return Inertia::render('Peternak/Hewan/Edit', [
            'animal' => $animal,
            'regions' => $regions,
            'minAges' => [
                'kambing' => $this->animalService->getMinAgeMonths('kambing'),
                'domba' => $this->animalService->getMinAgeMonths('domba'),
                'sapi' => $this->animalService->getMinAgeMonths('sapi'),
            ],
        ]);
    }

    public function update(Request $request, Animal $animal): RedirectResponse
    {
        $profile = $request->user()->peternakProfile;
        if (! $profile || $animal->peternak_profile_id !== $profile->id) {
            abort(403);
        }

        if ($animal->status === AnimalStatus::DIPESAN || $animal->status === AnimalStatus::TERJUAL) {
            return back()->withErrors(['error' => 'Hewan yang sedang direservasi atau telah terjual tidak dapat diubah (BR-10).']);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AnimalType::class)],
            'age_months' => ['required', 'integer', 'min:1'],
            'weight_kg' => ['required', 'numeric', 'min:10', 'max:2000'],
            'price' => ['required', 'integer', 'min:1000000'],
            'region_id' => ['required', 'exists:regions,id'],
            'health_notes' => ['required', 'string', 'max:2000'],
        ]);

        $this->animalService->validateAnimalAge($validated['type'], (int) $validated['age_months']);

        $animal->update([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'age_months' => (int) $validated['age_months'],
            'weight_kg' => $validated['weight_kg'],
            'price' => (int) $validated['price'],
            'region_id' => $validated['region_id'],
            'health_notes' => $validated['health_notes'],
            'status' => AnimalStatus::MENUNGGU_REVIEW, // re-submits for admin review
        ]);

        return redirect()->route('peternak.hewan.index')
            ->with('success', 'Data hewan kurban berhasil diperbarui.');
    }
}

