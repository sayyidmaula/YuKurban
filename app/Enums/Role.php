<?php

namespace App\Enums;

enum Role: string
{
    case PENABUNG = 'penabung';
    case PETERNAK = 'peternak';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::PENABUNG => 'Penabung',
            self::PETERNAK => 'Peternak',
            self::ADMIN => 'Admin',
        };
    }
}

