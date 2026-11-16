<?php

namespace App\Enums;

enum OrderMethod: string
{
    case SEMBELIH_TIM = 'sembelih_tim';
    case KIRIM_HIDUP = 'kirim_hidup';

    public function label(): string
    {
        return match ($this) {
            self::SEMBELIH_TIM => 'Sembelih Tim (Daging Disalurkan)',
            self::KIRIM_HIDUP => 'Kirim Hewan Hidup (Sembelih Mandiri)',
        };
    }
}

