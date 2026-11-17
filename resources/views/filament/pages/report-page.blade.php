<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm space-y-4">
            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">
                Pusat Unduhan Laporan Transparan YuKurban
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                Platform YuKurban mengedepankan prinsip amanah, transparansi penuh, dan akuntabilitas syariah.
                Anda dapat mengekspor seluruh rekapitulasi data transaksi dalam format CSV melalui tombol ekspor di atas:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 space-y-1">
                    <span class="text-xs font-bold uppercase text-primary-600 dark:text-primary-400">1. Laporan Pesanan</span>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Memuat nomor pesanan, metode sembelih tim vs kirim hidup, rincian biaya, ujrah, ongkir, dan komisi platform.
                    </p>
                </div>

                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 space-y-1">
                    <span class="text-xs font-bold uppercase text-success-600 dark:text-success-400">2. Laporan Buku Besar (Ledger)</span>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Memuat riwayat transaksi append-only: setoran, pembelian, penarikan, pengembalian dana, dan saldo berjalan.
                    </p>
                </div>

                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 space-y-1">
                    <span class="text-xs font-bold uppercase text-warning-600 dark:text-warning-400">3. Laporan Payout Mitra</span>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Memuat status dana escrow: tertahan, dibayarkan, atau dibatalkan, serta potongan komisi platform transparan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
