<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Kunci Pengaturan')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->required(),
                Textarea::make('value')
                    ->label('Nilai Konfigurasi (JSON atau Nilai Teks)')
                    ->rows(6)
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : (is_string($state) ? $state : json_encode($state)))
                    ->dehydrateStateUsing(function ($state) {
                        $decoded = json_decode($state, true);

                        return json_last_error() === JSON_ERROR_NONE ? $decoded : $state;
                    })
                    ->required(),
            ]);
    }
}
