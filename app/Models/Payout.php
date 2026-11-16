<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'peternak_profile_id',
        'amount',
        'commission',
        'status',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'commission' => 'integer',
            'status' => PayoutStatus::class,
            'released_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function peternakProfile(): BelongsTo
    {
        return $this->belongsTo(PeternakProfile::class);
    }
}

