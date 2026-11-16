<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_no')
                    ->label('Nomor Pesanan')
                    ->disabled(),
                TextInput::make('method')
                    ->label('Metode')
                    ->disabled(),
                TextInput::make('status')
                    ->label('Status')
                    ->disabled(),
                TextInput::make('total_buyer_pays')
                    ->label('Total Bayar Pembeli')
                    ->disabled(),
                DateTimePicker::make('scheduled_at')
                    ->label('Jadwal Pelaksanaan'),
                DateTimePicker::make('slaughtered_at')
                    ->label('Waktu Sembelih'),
                DateTimePicker::make('delivered_at')
                    ->label('Waktu Penyaluran / Diterima'),
                DateTimePicker::make('auto_confirm_at')
                    ->label('Batas Konfirmasi Otomatis'),
                Textarea::make('distribution_note')
                    ->label('Catatan Penyaluran Daging')
                    ->columnSpanFull(),
            ]);
    }
}
