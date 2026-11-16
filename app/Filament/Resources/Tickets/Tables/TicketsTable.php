<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Enums\TicketDecision;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Ticket;
use App\Services\TicketService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state?->value ?? $state) {
                        'pertanyaan_prabeli' => 'Pra-Beli',
                        'laporan_masalah' => 'Laporan Masalah',
                        'bantuan_peternak' => 'Bantuan Peternak',
                        default => $state,
                    })
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        'laporan_masalah' => 'danger',
                        'pertanyaan_prabeli' => 'info',
                        default => 'primary',
                    }),
                TextColumn::make('creator.name')
                    ->label('Pengaju')
                    ->searchable(),
                TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        'dibuka' => 'warning',
                        'ditanggapi' => 'info',
                        'diputuskan' => 'primary',
                        'selesai' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('decision')
                    ->label('Keputusan')
                    ->badge()
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        'dana_kembali', 'ganti_hewan' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe Tiket')
                    ->options([
                        'pertanyaan_prabeli' => 'Pertanyaan Pra-Beli',
                        'laporan_masalah' => 'Laporan Masalah',
                        'bantuan_peternak' => 'Bantuan Peternak',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'dibuka' => 'Dibuka',
                        'ditanggapi' => 'Ditanggapi',
                        'diputuskan' => 'Diputuskan',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                    ]),
            ])
            ->recordActions([
                // 1. Balas Tiket
                Action::make('reply')
                    ->label('Balas')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->color('primary')
                    ->form([
                        Textarea::make('body')
                            ->label('Isi Balasan Admin')
                            ->required(),
                        Checkbox::make('is_faq_public')
                            ->label('Tandai sebagai FAQ Publik (tanpa identitas penanya)'),
                    ])
                    ->action(function (Ticket $record, array $data): void {
                        app(TicketService::class)->replyTicket($record, auth()->user(), $data['body'], $data['is_faq_public'] ?? false);
                    }),

                // 2. Putuskan Laporan Masalah
                Action::make('decide')
                    ->label('Beri Keputusan')
                    ->icon('heroicon-o-scale')
                    ->color('warning')
                    ->visible(fn (Ticket $record): bool => $record->type === TicketType::LAPORAN_MASALAH && in_array($record->status, [TicketStatus::DIBUKA, TicketStatus::DITANGGAPI], true))
                    ->form([
                        Select::make('decision')
                            ->label('Keputusan Sengketa')
                            ->options([
                                'dana_kembali' => 'Dana Kembali (Refund Penuh ke Tabungan)',
                                'ganti_hewan' => 'Ganti Hewan Kurban',
                                'ditolak' => 'Tolak Laporan',
                            ])
                            ->required(),
                        Textarea::make('decision_note')
                            ->label('Alasan / Catatan Putusan')
                            ->required(),
                    ])
                    ->action(function (Ticket $record, array $data): void {
                        app(TicketService::class)->decideLaporanMasalah(
                            $record,
                            TicketDecision::from($data['decision']),
                            $data['decision_note'],
                            auth()->user()
                        );
                    }),

                // 3. Tutup Tiket
                Action::make('close')
                    ->label('Tutup Tiket')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Ticket $record): bool => $record->status !== TicketStatus::SELESAI)
                    ->requiresConfirmation()
                    ->action(function (Ticket $record): void {
                        app(TicketService::class)->closeTicket($record, auth()->user());
                    }),

                EditAction::make(),
            ]);
    }
}
