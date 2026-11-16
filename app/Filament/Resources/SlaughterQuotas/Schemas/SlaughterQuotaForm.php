<?php

namespace App\Filament\Resources\SlaughterQuotas\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SlaughterQuotaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Tanggal Sembelih')
                    ->required(),
                TextInput::make('quota')
                    ->label('Kapasitas Kuota Harian (Ekor)')
                    ->required()
                    ->numeric()
                    ->minValue(1),
            ]);
    }
}
