<?php

namespace App\Filament\Resources\Payouts\Tables;

use App\Enums\PayoutStatus;
use App\Models\Payout;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('order.order_no')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('peternakProfile.farm_name')
                    ->label('Kandang Mitra')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Nominal Bersih Peternak')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                TextColumn::make('commission')
                    ->label('Komisi Platform')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        'tertahan' => 'warning',
                        'dibayarkan' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('released_at')
                    ->label('Waktu Pencairan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'tertahan' => 'Tertahan (Escrow)',
                        'dibayarkan' => 'Dibayarkan',
                        'dibatalkan' => 'Dibatalkan',
                    ]),
            ])
            ->recordActions([
                Action::make('release')
                    ->label('Simulasi Pencairan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payout $record): bool => $record->status === PayoutStatus::TERTAHAN)
                    ->requiresConfirmation()
                    ->action(function (Payout $record): void {
                        $record->update([
                            'status' => PayoutStatus::DIBAYARKAN,
                            'released_at' => now(),
                        ]);
                    }),
            ]);
    }
}
