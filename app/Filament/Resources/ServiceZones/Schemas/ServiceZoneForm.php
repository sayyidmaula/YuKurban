<?php

namespace App\Filament\Resources\ServiceZones\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('region_id')
                    ->relationship('region', 'city')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->city}, {$record->province}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Wilayah'),
                Toggle::make('supports_sembelih_tim')
                    ->label('Mendukung Sembelih Tim')
                    ->default(true)
                    ->required(),
                Toggle::make('supports_kirim_hidup')
                    ->label('Mendukung Kirim Hidup')
                    ->default(true)
                    ->required(),
                TextInput::make('delivery_fee')
                    ->label('Ongkir Pengiriman (Rp)')
                    ->prefix('Rp')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
