<?php

namespace App\Filament\Resources\Animals\Schemas;

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AnimalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('peternak_profile_id')
                    ->relationship('peternakProfile', 'farm_name')
                    ->searchable()
                    ->preload()
                    ->label('Kandang / Peternakan')
                    ->required(),
                Select::make('type')
                    ->label('Jenis Hewan')
                    ->options([
                        'kambing' => 'Kambing',
                        'domba' => 'Domba',
                        'sapi' => 'Sapi',
                    ])
                    ->required(),
                TextInput::make('title')
                    ->label('Nama / Judul Hewan')
                    ->required(),
                TextInput::make('age_months')
                    ->label('Umur (Bulan)')
                    ->required()
                    ->numeric(),
                TextInput::make('weight_kg')
                    ->label('Bobot (Kg)')
                    ->required()
                    ->numeric(),
                Textarea::make('health_notes')
                    ->label('Catatan Kesehatan')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Harga Hewan (Rp)')
                    ->prefix('Rp')
                    ->required()
                    ->numeric(),
                Select::make('region_id')
                    ->relationship('region', 'city')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->city}, {$record->province}")
                    ->searchable()
                    ->preload()
                    ->label('Wilayah Asal')
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'menunggu_review' => 'Menunggu Review',
                        'tayang' => 'Tayang',
                        'dipesan' => 'Dipesan',
                        'terjual' => 'Terjual',
                        'ditarik' => 'Ditarik',
                    ])
                    ->default('menunggu_review')
                    ->required(),
                Textarea::make('review_note')
                    ->label('Catatan Review Admin')
                    ->columnSpanFull(),
                DateTimePicker::make('reviewed_at')
                    ->label('Waktu Direview'),
            ]);
    }
}
