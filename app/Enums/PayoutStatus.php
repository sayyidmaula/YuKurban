<?php

namespace App\Enums;

enum PayoutStatus: string
{
    case TERTAHAN = 'tertahan';
    case DIBAYARKAN = 'dibayarkan';
    case DIBATALKAN = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::TERTAHAN => 'Dana Tertahan (Escrow)',
            self::DIBAYARKAN => 'Dibayarkan',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }
}

