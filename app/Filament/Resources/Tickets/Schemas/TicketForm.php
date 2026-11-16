<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('type')
                    ->label('Tipe Tiket')
                    ->disabled(),
                TextInput::make('subject')
                    ->label('Subjek')
                    ->disabled(),
                TextInput::make('category')
                    ->label('Kategori')
                    ->disabled(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'dibuka' => 'Dibuka',
                        'ditanggapi' => 'Ditanggapi',
                        'diputuskan' => 'Diputuskan',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                    ])
                    ->required(),
                Select::make('decision')
                    ->label('Keputusan')
                    ->options([
                        'ganti_hewan' => 'Ganti Hewan',
                        'dana_kembali' => 'Dana Kembali (Refund)',
                        'ditolak' => 'Ditolak',
                    ]),
                Textarea::make('decision_note')
                    ->label('Catatan Keputusan')
                    ->columnSpanFull(),
            ]);
    }
}
