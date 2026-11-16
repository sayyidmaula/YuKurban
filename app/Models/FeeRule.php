<?php

namespace App\Models;

use App\Enums\AnimalType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_type',
        'fee_slaughter',
        'fee_distribution',
        'commission_peternak',
    ];

    protected function casts(): array
    {
        return [
            'animal_type' => AnimalType::class,
            'fee_slaughter' => 'integer',
            'fee_distribution' => 'integer',
            'commission_peternak' => 'integer',
        ];
    }

    public static function getForAnimalType(AnimalType|string $type): ?self
    {
        $val = $type instanceof AnimalType ? $type->value : $type;

        return static::where('animal_type', $val)->first();
    }
}
