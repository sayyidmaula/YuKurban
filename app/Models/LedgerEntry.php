<?php

namespace App\Models;

use App\Enums\LedgerType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class LedgerEntry extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'savings_plan_id',
        'user_id',
        'type',
        'direction',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'description',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => LedgerType::class,
            'amount' => 'integer',
            'balance_after' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('Ledger is append-only. Updating ledger entries is strictly forbidden (BR-01).');
        });

        static::deleting(function () {
            throw new RuntimeException('Ledger is append-only. Deleting ledger entries is strictly forbidden (BR-01).');
        });
    }

    public function savingsPlan(): BelongsTo
    {
        return $this->belongsTo(SavingsPlan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

