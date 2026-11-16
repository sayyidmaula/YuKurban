<?php

namespace App\Enums;

enum AnimalStatus: string
{
    case DRAFT = 'draft';
    case MENUNGGU_REVIEW = 'menunggu_review';
    case TAYANG = 'tayang';
    case DIPESAN = 'dipesan';
    case TERJUAL = 'terjual';
    case DITARIK = 'ditarik';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::MENUNGGU_REVIEW => 'Menunggu Review Admin',
            self::TAYANG => 'Tayang di Katalog',
            self::DIPESAN => 'Direservasi / Dipesan',
            self::TERJUAL => 'Terjual',
            self::DITARIK => 'Ditarik dari Katalog',
        };
    }
}

