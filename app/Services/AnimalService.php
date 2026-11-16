<?php

namespace App\Services;

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use App\Models\Animal;
use App\Models\AnimalMedia;
use App\Models\PeternakProfile;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class AnimalService
{
    /**
     * Get minimum age required for animal type from settings (BR-16).
     */
    public function getMinAgeMonths(AnimalType|string $type): int
    {
        $typeKey = $type instanceof AnimalType ? $type->value : $type;
        $minAges = Setting::get('min_age_months', [
            'kambing' => 12,
            'domba' => 12,
            'sapi' => 24,
        ]);

        return (int) ($minAges[$typeKey] ?? 12);
    }

    /**
     * Validate animal age against settings (BR-16).
     */
    public function validateAnimalAge(AnimalType|string $type, int $ageMonths): void
    {
        $minAge = $this->getMinAgeMonths($type);
        if ($ageMonths < $minAge) {
            $typeName = $type instanceof AnimalType ? $type->label() : ucfirst($type);
            throw ValidationException::withMessages([
                'age_months' => "Umur minimum untuk hewan kurban jenis {$typeName} adalah {$minAge} bulan sesuai rujukan resmi syariat (BR-16).",
            ]);
        }
    }

    /**
     * Verify Peternak profile by admin.
     */
    public function verifyPeternak(PeternakProfile $profile, User $admin, bool $approved, ?string $reason = null): PeternakProfile
    {
        if ($approved) {
            $profile->update([
                'status' => 'terverifikasi',
                'rejection_reason' => null,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);
        } else {
            $profile->update([
                'status' => 'ditolak',
                'rejection_reason' => $reason ?? 'Data peternak belum memenuhi kelayakan verifikasi.',
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);
        }

        return $profile->fresh();
    }

    /**
     * Review Animal by admin.
     */
    public function reviewAnimal(Animal $animal, User $admin, bool $approved, ?string $note = null): Animal
    {
        if ($approved) {
            // Check BR-16: minimal 3 foto, 1 video
            $photosCount = $animal->photos()->count();
            $videosCount = $animal->videos()->count();

            if ($photosCount < 3 || $videosCount < 1) {
                throw new InvalidArgumentException("Hewan belum memenuhi syarat BR-16 (minimal 3 foto dan 1 video fisik). Foto: {$photosCount}, Video: {$videosCount}.");
            }

            $animal->update([
                'status' => AnimalStatus::TAYANG,
                'review_note' => $note,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
        } else {
            $animal->update([
                'status' => AnimalStatus::DRAFT,
                'review_note' => $note ?? 'Hewan ditolak/memerlukan revisi oleh admin.',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
        }

        return $animal->fresh();
    }
}

