<?php

namespace App\Filament\Resources\SlaughterQuotas\Tables;

use App\Models\SlaughterQuota;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SlaughterQuotasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal Sembelih')
                    ->date('l, d F Y')
                    ->sortable(),
                TextColumn::make('quota')
                    ->label('Total Kuota')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('remaining')
                    ->label('Sisa Kuota')
                    ->state(fn ($record) => SlaughterQuota::getRemainingQuota($record->date))
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
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
