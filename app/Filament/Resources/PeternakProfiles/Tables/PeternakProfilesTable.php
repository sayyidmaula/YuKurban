<?php

namespace App\Filament\Resources\PeternakProfiles\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PeternakProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Peternak')
                    ->searchable(),
                TextColumn::make('farm_name')
                    ->label('Nama Kandang')
                    ->searchable(),
                TextColumn::make('region.city')
                    ->label('Wilayah')
                    ->description(fn ($record) => $record->region?->province)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'terverifikasi' => 'success',
                        'menunggu' => 'warning',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('verified_at')
                    ->label('Diverifikasi Pada')
                    ->dateTime()
                    ->sortable(),
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
                Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== 'terverifikasi')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'terverifikasi',
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                            'rejection_reason' => null,
                        ]);
                    }),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->visible(fn ($record) => $record->status !== 'ditolak')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'ditolak',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
