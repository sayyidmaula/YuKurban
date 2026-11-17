<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('actor.name')
                    ->label('Aktor')
                    ->searchable()
                    ->placeholder('Sistem / Otomatis'),
                TextColumn::make('action')
                    ->label('Tindakan / Aksi')
                    ->searchable()
                    ->badge()
                    ->weight('bold'),
                TextColumn::make('subject_type')
                    ->label('Objek Terkait')
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '-'),
                TextColumn::make('subject_id')
                    ->label('ID Objek')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ]);
    }
}
