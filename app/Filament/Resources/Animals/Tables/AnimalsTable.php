<?php

namespace App\Filament\Resources\Animals\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnimalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Nama Hewan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('peternakProfile.farm_name')
                    ->label('Kandang')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state?->value ?? $state)),
                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('region.city')
                    ->label('Wilayah')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        'tayang' => 'success',
                        'menunggu_review' => 'warning',
                        'dipesan' => 'info',
                        'terjual' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Setujui & Tayang')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status->value ?? $record->status, ['menunggu_review', 'draft']))
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'tayang',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'review_note' => 'Disetujui oleh admin.',
                        ]);
                    }),
                Action::make('reject')
                    ->label('Tolak / Minta Revisi')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('review_note')
                            ->label('Catatan Review / Alasan')
                            ->required(),
                    ])
                    ->visible(fn ($record) => ($record->status->value ?? $record->status) === 'menunggu_review')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'draft',
                            'review_note' => $data['review_note'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
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
