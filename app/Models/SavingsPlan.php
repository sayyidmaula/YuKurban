<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'tier_id',
        'target_amount',
        'target_year',
        'monthly_amount',
        'status',
        'autodebet_enabled',
        'autodebet_day',
        'balance_cached',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'integer',
            'target_year' => 'integer',
            'monthly_amount' => 'integer',
            'balance_cached' => 'integer',
            'autodebet_enabled' => 'boolean',
            'autodebet_day' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(PriceTier::class, 'tier_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class)->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    public function paymentInvoices(): HasMany
    {
        return $this->hasMany(PaymentInvoice::class)->orderBy('created_at', 'desc');
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->balance_cached / $this->target_amount) * 100));
    }
}

