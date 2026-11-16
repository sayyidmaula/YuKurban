<?php

namespace App\Filament\Resources\ServiceZones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('region.city')
                    ->label('Wilayah')
                    ->description(fn ($record) => $record->region?->province)
                    ->searchable()
                    ->sortable(),
                IconColumn::make('supports_sembelih_tim')
                    ->label('Sembelih Tim')
                    ->boolean(),
                IconColumn::make('supports_kirim_hidup')
                    ->label('Kirim Hidup')
                    ->boolean(),
                TextColumn::make('delivery_fee')
                    ->label('Ongkos Kirim')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
