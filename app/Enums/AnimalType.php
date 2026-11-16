<?php

namespace App\Enums;

enum AnimalType: string
{
    case KAMBING = 'kambing';
    case DOMBA = 'domba';
    case SAPI = 'sapi';

    public function label(): string
    {
        return match ($this) {
            self::KAMBING => 'Kambing',
            self::DOMBA => 'Domba',
            self::SAPI => 'Sapi',
        };
    }
}

