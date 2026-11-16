<?php

namespace App\Enums;

enum TicketDecision: string
{
    case GANTI_HEWAN = 'ganti_hewan';
    case DANA_KEMBALI = 'dana_kembali';
    case DITOLAK = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::GANTI_HEWAN => 'Ganti Hewan Kurban',
            self::DANA_KEMBALI => 'Dana Kembali (Refund Penuh)',
            self::DITOLAK => 'Laporan Ditolak',
        };
    }
}
