<?php

namespace App\Filament\Resources\LedgerEntries\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LedgerEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('savingsPlan.name')
                    ->label('Rencana Tabungan')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Penabung')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state?->value ?? $state))),
                TextColumn::make('direction')
                    ->label('Arah')
                    ->badge()
                    ->color(fn ($state) => $state === 'masuk' ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('balance_after')
                    ->label('Saldo Pasca Transaksi')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(40),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'setoran' => 'Setoran',
                        'pembelian' => 'Pembelian',
                        'pengembalian_dana' => 'Pengembalian Dana',
                        'penarikan' => 'Penarikan',
                        'koreksi' => 'Koreksi',
                    ]),
                SelectFilter::make('direction')
                    ->options([
                        'masuk' => 'Masuk',
                        'keluar' => 'Keluar',
                    ]),
            ]);
    }
}
