<?php

namespace App\Models;

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Animal extends Model
{
    use HasFactory;

    protected $fillable = [
        'peternak_profile_id',
        'type',
        'title',
        'age_months',
        'weight_kg',
        'health_notes',
        'price',
        'region_id',
        'status',
        'reserved_by',
        'reserved_until',
        'review_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => AnimalType::class,
            'status' => AnimalStatus::class,
            'age_months' => 'integer',
            'weight_kg' => 'float',
            'price' => 'integer',
            'reserved_until' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function peternakProfile(): BelongsTo
    {
        return $this->belongsTo(PeternakProfile::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(AnimalMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AnimalMedia::class)->where('type', 'foto')->orderBy('sort_order');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(AnimalMedia::class)->where('type', 'video')->orderBy('sort_order');
    }

    public function reserver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeTayang(Builder $query): Builder
    {
        return $query->where('status', AnimalStatus::TAYANG);
    }

    public function isTayang(): bool
    {
        return $this->status === AnimalStatus::TAYANG;
    }

    public function isAvailable(): bool
    {
        return $this->isTayang() && ($this->reserved_until === null || $this->reserved_until->isPast());
    }
}

