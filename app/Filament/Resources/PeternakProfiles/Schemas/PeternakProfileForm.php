<?php

namespace App\Filament\Resources\PeternakProfiles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PeternakProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Nama Akun Peternak')
                    ->required(),
                TextInput::make('farm_name')
                    ->label('Nama Peternakan / Kandang')
                    ->required(),
                Textarea::make('address')
                    ->label('Alamat Kandang')
                    ->required()
                    ->columnSpanFull(),
                Select::make('region_id')
                    ->relationship('region', 'city')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->city}, {$record->province}")
                    ->searchable()
                    ->preload()
                    ->label('Wilayah')
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi Peternakan')
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Status Verifikasi')
                    ->options([
                        'menunggu' => 'Menunggu',
                        'terverifikasi' => 'Terverifikasi',
                        'ditolak' => 'Ditolak',
                        'nonaktif' => 'Nonaktif',
                    ])
                    ->required()
                    ->default('menunggu'),
                Textarea::make('rejection_reason')
                    ->label('Alasan Penolakan (jika ditolak)')
                    ->columnSpanFull(),
                DateTimePicker::make('verified_at')
                    ->label('Waktu Diverifikasi'),
            ]);
    }
}
