<?php

namespace App\Models;

use App\Enums\OrderMethod;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'user_id',
        'savings_plan_id',
        'animal_id',
        'region_id',
        'method',
        'status',
        'animal_price',
        'fee_slaughter',
        'fee_distribution',
        'fee_delivery',
        'commission',
        'total_buyer_pays',
        'reserved_until',
        'wakalah_agreed_at',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'distribution_note',
        'peternak_ready_at',
        'scheduled_at',
        'slaughtered_at',
        'delivered_at',
        'confirmed_at',
        'auto_confirm_at',
        'is_disputed',
    ];

    protected function casts(): array
    {
        return [
            'method' => OrderMethod::class,
            'status' => OrderStatus::class,
            'animal_price' => 'integer',
            'fee_slaughter' => 'integer',
            'fee_distribution' => 'integer',
            'fee_delivery' => 'integer',
            'commission' => 'integer',
            'total_buyer_pays' => 'integer',
            'reserved_until' => 'datetime',
            'wakalah_agreed_at' => 'datetime',
            'peternak_ready_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'slaughtered_at' => 'datetime',
            'delivered_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'auto_confirm_at' => 'datetime',
            'is_disputed' => 'boolean',
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

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at', 'asc');
    }

    public function payout(): HasOne
    {
        return $this->hasOne(Payout::class);
    }

    public function isSembelihTim(): bool
    {
        return $this->method === OrderMethod::SEMBELIH_TIM;
    }

    public function isKirimHidup(): bool
    {
        return $this->method === OrderMethod::KIRIM_HIDUP;
    }
}

