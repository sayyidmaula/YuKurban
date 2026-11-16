<?php

namespace App\Enums;

enum TicketStatus: string
{
    case DIBUKA = 'dibuka';
    case DITANGGAPI = 'ditanggapi';
    case DIPUTUSKAN = 'diputuskan';
    case SELESAI = 'selesai';
    case DITOLAK = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::DIBUKA => 'Dibuka',
            self::DITANGGAPI => 'Ditanggapi Admin',
            self::DIPUTUSKAN => 'Diputuskan',
            self::SELESAI => 'Selesai',
            self::DITOLAK => 'Ditolak',
        };
    }
}
