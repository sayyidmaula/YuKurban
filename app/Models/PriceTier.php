<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_price',
        'max_price',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_price' => 'integer',
            'max_price' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function savingsPlans(): HasMany
    {
        return $this->hasMany(SavingsPlan::class, 'tier_id');
    }
}
