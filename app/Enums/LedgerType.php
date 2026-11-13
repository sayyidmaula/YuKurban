<?php

namespace App\Enums;

enum LedgerType: string
{
    case SETORAN = 'setoran';
    case PEMBELIAN = 'pembelian';
    case PENGEMBALIAN_DANA = 'pengembalian_dana';
    case PENARIKAN = 'penarikan';
    case KOREKSI = 'koreksi';

    public function direction(): string
    {
        return match ($this) {
            self::SETORAN, self::PENGEMBALIAN_DANA => 'masuk',
            self::PEMBELIAN, self::PENARIKAN => 'keluar',
            self::KOREKSI => 'masuk', // or evaluated dynamically
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SETORAN => 'Setoran Tabungan',
            self::PEMBELIAN => 'Pembelian Kurban',
            self::PENGEMBALIAN_DANA => 'Pengembalian Dana (Refund)',
            self::PENARIKAN => 'Penarikan Dana',
            self::KOREKSI => 'Koreksi Pembukuan',
        };
    }
}
