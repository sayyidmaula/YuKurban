<?php

namespace App\Enums;

enum TicketType: string
{
    case PERTANYAAN_PRABELI = 'pertanyaan_prabeli';
    case LAPORAN_MASALAH = 'laporan_masalah';
    case BANTUAN_PETERNAK = 'bantuan_peternak';

    public function label(): string
    {
        return match ($this) {
            self::PERTANYAAN_PRABELI => 'Pertanyaan Pra-Beli',
            self::LAPORAN_MASALAH => 'Laporan Masalah Pesanan',
            self::BANTUAN_PETERNAK => 'Bantuan Kemitraan Peternak',
        };
    }
}
