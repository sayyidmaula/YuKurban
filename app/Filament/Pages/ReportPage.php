<?php

namespace App\Filament\Pages;

use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Payout;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class ReportPage extends Page
{
    protected static ?string $navigationLabel = 'Laporan Transparan';

    protected static string|UnitEnum|null $navigationGroup = 'Sistem & Laporan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected string $view = 'filament.pages.report-page';

    public function exportOrdersCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan_pesanan_' . now()->format('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'No. Pesanan',
                'Metode',
                'Status',
                'Harga Hewan',
                'Ujrah Sembelih',
                'Ujrah Distribusi',
                'Ongkir',
                'Komisi Platform',
                'Total Bayar Pembeli',
                'Tanggal Pesan',
            ]);

            Order::chunk(100, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->id,
                        $order->order_no,
                        $order->method->value ?? $order->method,
                        $order->status->value ?? $order->status,
                        $order->animal_price,
                        $order->fee_slaughter,
                        $order->fee_distribution,
                        $order->fee_delivery,
                        $order->commission,
                        $order->total_buyer_pays,
                        $order->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function exportLedgerCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan_ledger_' . now()->format('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'ID Rencana',
                'Jenis Transaksi',
                'Arah',
                'Nominal',
                'Saldo Pasca Transaksi',
                'Keterangan',
                'Waktu',
            ]);

            LedgerEntry::chunk(100, function ($entries) use ($handle) {
                foreach ($entries as $entry) {
                    fputcsv($handle, [
                        $entry->id,
                        $entry->savings_plan_id,
                        $entry->type->value ?? $entry->type,
                        $entry->direction,
                        $entry->amount,
                        $entry->balance_after,
                        $entry->description,
                        $entry->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function exportPayoutsCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan_payout_' . now()->format('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'ID Pesanan',
                'ID Mitra Peternak',
                'Nominal Payout Bersih',
                'Komisi Platform',
                'Status',
                'Waktu Dicairkan',
                'Tanggal Dibuat',
            ]);

            Payout::chunk(100, function ($payouts) use ($handle) {
                foreach ($payouts as $p) {
                    fputcsv($handle, [
                        $p->id,
                        $p->order_id,
                        $p->peternak_profile_id,
                        $p->amount,
                        $p->commission,
                        $p->status->value ?? $p->status,
                        $p->released_at?->format('Y-m-d H:i:s') ?? '-',
                        $p->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportOrders')
                ->label('Ekspor CSV Pesanan')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action('exportOrdersCsv'),
            Action::make('exportLedger')
                ->label('Ekspor CSV Ledger')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportLedgerCsv'),
            Action::make('exportPayouts')
                ->label('Ekspor CSV Payout')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->action('exportPayoutsCsv'),
        ];
    }
}
