<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_no')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('animal.title')
                    ->label('Hewan')
                    ->searchable(),
                TextColumn::make('method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'sembelih_tim' ? 'Sembelih Tim' : 'Kirim Hidup')
                    ->color(fn ($state) => $state === 'sembelih_tim' ? 'primary' : 'info'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        'direservasi' => 'gray',
                        'dikonfirmasi' => 'warning',
                        'dijadwalkan', 'dijadwalkan_kirim' => 'info',
                        'disembelih', 'dalam_perjalanan' => 'primary',
                        'disalurkan', 'diterima' => 'success',
                        'selesai' => 'success',
                        'kedaluwarsa', 'dibatalkan' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('total_buyer_pays')
                    ->label('Total Bayar')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label('Jadwal')
                    ->dateTime('d M Y')
                    ->sortable(),
                IconColumn::make('is_disputed')
                    ->label('Sengketa')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('danger')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->label('Dipesan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('method')
                    ->label('Metode')
                    ->options([
                        'sembelih_tim' => 'Sembelih Tim',
                        'kirim_hidup' => 'Kirim Hewan Hidup',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'direservasi' => 'Direservasi',
                        'dikonfirmasi' => 'Dikonfirmasi',
                        'dijadwalkan' => 'Dijadwalkan Sembelih',
                        'disembelih' => 'Disembelih',
                        'disalurkan' => 'Disalurkan',
                        'dijadwalkan_kirim' => 'Dijadwalkan Kirim',
                        'dalam_perjalanan' => 'Dalam Perjalanan',
                        'diterima' => 'Diterima',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                        'kedaluwarsa' => 'Kedaluwarsa',
                    ]),
            ])
            ->recordActions([
                // 1. Sembelih Tim: Jadwalkan
                Action::make('scheduleSembelih')
                    ->label('Jadwalkan Sembelih')
                    ->icon('heroicon-o-calendar')
                    ->color('warning')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::DIKONFIRMASI && $record->isSembelihTim())
                    ->form([
                        DatePicker::make('date')
                            ->label('Tanggal Sembelih')
                            ->required(),
                        Textarea::make('note')
                            ->label('Catatan Jadwal'),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(OrderService::class)->scheduleSembelihTim($record, $data['date'], auth()->user(), $data['note'] ?? null);
                    }),

                // 2. Sembelih Tim: Tandai Disembelih
                Action::make('markSlaughtered')
                    ->label('Tandai Disembelih')
                    ->icon('heroicon-o-check')
                    ->color('primary')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::DIJADWALKAN && $record->isSembelihTim())
                    ->form([
                        Textarea::make('note')
                            ->label('Catatan Penyembelihan'),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(OrderService::class)->markSlaughtered($record, auth()->user(), $data['note'] ?? null);
                    }),

                // 3. Sembelih Tim: Tandai Disalurkan
                Action::make('markDistributed')
                    ->label('Tandai Disalurkan')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::DISEMBELIH && $record->isSembelihTim())
                    ->form([
                        Textarea::make('note')
                            ->label('Catatan Penyaluran'),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(OrderService::class)->markDistributed($record, auth()->user(), $data['note'] ?? null);
                    }),

                // 4. Kirim Hidup: Jadwalkan Kirim
                Action::make('scheduleKirim')
                    ->label('Jadwalkan Kirim')
                    ->icon('heroicon-o-calendar')
                    ->color('warning')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::DIKONFIRMASI && $record->isKirimHidup())
                    ->form([
                        DatePicker::make('date')
                            ->label('Tanggal Pengiriman')
                            ->required(),
                        Textarea::make('note')
                            ->label('Catatan Jadwal Kirim'),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(OrderService::class)->scheduleKirimHidup($record, $data['date'], auth()->user(), $data['note'] ?? null);
                    }),

                // 5. Kirim Hidup: Dalam Perjalanan
                Action::make('markInTransit')
                    ->label('Dalam Perjalanan')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('primary')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::DIJADWALKAN_KIRIM && $record->isKirimHidup())
                    ->form([
                        Textarea::make('note')
                            ->label('Catatan Keberangkatan'),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(OrderService::class)->markInTransit($record, auth()->user(), $data['note'] ?? null);
                    }),

                // 6. Kirim Hidup: Diterima
                Action::make('markDelivered')
                    ->label('Tandai Diterima')
                    ->icon('heroicon-o-map-pin')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::DALAM_PERJALANAN && $record->isKirimHidup())
                    ->form([
                        Textarea::make('note')
                            ->label('Catatan Penerimaan'),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(OrderService::class)->markDelivered($record, auth()->user(), $data['note'] ?? null);
                    }),

                // 7. Selesaikan Pesanan & Rilis Payout
                Action::make('completeOrder')
                    ->label('Selesaikan & Rilis Payout')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Order $record): bool => in_array($record->status, [OrderStatus::DISALURKAN, OrderStatus::DITERIMA], true))
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        app(OrderService::class)->completeOrder($record, auth()->user(), 'Pesanan diselesaikan oleh admin.');
                    }),

                // 8. Unggah Bukti
                Action::make('addEvidence')
                    ->label('Unggah Bukti')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->form([
                        Select::make('stage')
                            ->label('Tahapan')
                            ->options([
                                'sembelih' => 'Penyembelihan',
                                'penyaluran' => 'Penyaluran Daging',
                                'berangkat' => 'Keberangkatan Hewan',
                                'tiba' => 'Kedatangan Hewan',
                            ])
                            ->required(),
                        Select::make('type')
                            ->label('Jenis Bukti')
                            ->options([
                                'foto' => 'Foto',
                                'video' => 'Video',
                                'dokumen' => 'Dokumen',
                            ])
                            ->required(),
                        TextInput::make('path')
                            ->label('Path / URL Bukti')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(OrderService::class)->addEvidence($record, $data['stage'], $data['type'], $data['path'], auth()->user());
                    }),

                // 9. Catatan Internal
                Action::make('addNote')
                    ->label('Catatan')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->form([
                        Textarea::make('body')
                            ->label('Isi Catatan')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(OrderService::class)->addNote($record, auth()->user(), $data['body']);
                    }),

                EditAction::make(),
            ]);
    }
}
