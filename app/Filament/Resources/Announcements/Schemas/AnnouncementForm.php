<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Pengumuman')
                    ->required()
                    ->maxLength(255),
                Select::make('audience')
                    ->label('Target Audiens')
                    ->options([
                        'semua' => 'Semua Pengguna',
                        'penabung' => 'Hanya Penabung',
                        'peternak' => 'Hanya Mitra Peternak',
                    ])
                    ->default('semua')
                    ->required(),
                DateTimePicker::make('published_at')
                    ->label('Waktu Publikasi')
                    ->default(now()),
                Textarea::make('body')
                    ->label('Isi Pengumuman')
                    ->rows(5)
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
