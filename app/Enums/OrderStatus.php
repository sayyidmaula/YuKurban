<?php

namespace App\Enums;

enum OrderStatus: string
{
    // Umum
    case DIRESERVASI = 'direservasi';
    case DIKONFIRMASI = 'dikonfirmasi';
    case SELESAI = 'selesai';
    case KEDALUWARSA = 'kedaluwarsa';
    case DIBATALKAN = 'dibatalkan';

    // Sembelih Tim
    case DIJADWALKAN = 'dijadwalkan';
    case DISEMBELIH = 'disembelih';
    case DISALURKAN = 'disalurkan';

    // Kirim Hidup
    case DIJADWALKAN_KIRIM = 'dijadwalkan_kirim';
    case DALAM_PERJALANAN = 'dalam_perjalanan';
    case DITERIMA = 'diterima';

    public function label(): string
    {
        return match ($this) {
            self::DIRESERVASI => 'Direservasi',
            self::DIKONFIRMASI => 'Dikonfirmasi',
            self::DIJADWALKAN => 'Dijadwalkan Sembelih',
            self::DISEMBELIH => 'Telah Disembelih',
            self::DISALURKAN => 'Daging Disalurkan',
            self::DIJADWALKAN_KIRIM => 'Dijadwalkan Kirim',
            self::DALAM_PERJALANAN => 'Dalam Perjalanan',
            self::DITERIMA => 'Hewan Diterima',
            self::SELESAI => 'Selesai',
            self::KEDALUWARSA => 'Kedaluwarsa',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::SELESAI, self::KEDALUWARSA, self::DIBATALKAN]);
    }
}

