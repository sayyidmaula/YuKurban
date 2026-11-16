<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'supports_sembelih_tim',
        'supports_kirim_hidup',
        'delivery_fee',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'supports_sembelih_tim' => 'boolean',
            'supports_kirim_hidup' => 'boolean',
            'delivery_fee' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
