<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'savings_plan_id',
        'amount',
        'va_code',
        'status',
        'expires_at',
        'paid_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savingsPlan(): BelongsTo
    {
        return $this->belongsTo(SavingsPlan::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'menunggu';
    }

    public function isExpired(): bool
    {
        return $this->status === 'kedaluwarsa' || ($this->isPending() && $this->expires_at->isPast());
    }
}
