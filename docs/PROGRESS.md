# Progress Pengerjaan YuKurban (Web + PWA)

Sumber kebenaran tunggal: `docs/PRD.md`

## Status Fase

- [x] **Fase 1**: Inisialisasi proyek, autentikasi, peran, layout dan token desain, PWA dasar.
- [~] **Fase 2**: Rencana tabungan, tagihan simulasi, `LedgerService`, dashboard, tarik dana, autodebet.
- [ ] **Fase 3**: Peternak dan hewan: profil, verifikasi, CRUD hewan + media, review admin, katalog.
- [ ] **Fase 4**: Wilayah, zona, biaya, jendela musim, kuota (Filament).
- [ ] **Fase 5**: Pembelian: reservasi, rincian biaya, konfirmasi, `OrderService`, payout.
- [ ] **Fase 6**: Status, bukti, timeline; dua alur metode.
- [ ] **Fase 7**: Tiket tiga tipe, catatan pesanan, notifikasi, pengumuman.
- [ ] **Fase 8**: Laporan, audit log, seeder lengkap, polesan UI, Lighthouse, pengujian.

## Langkah Fase 2
- Migrasi: `price_tiers`, `savings_plans`, `payment_invoices`, `ledger_entries`.
- Enums: `LedgerType` (`setoran`, `pembelian`, `pengembalian_dana`, `penarikan`, `koreksi`).
- Model:
  - `LedgerEntry`: tolak update dan delete (`static::updating`, `static::deleting`).
  - `SavingsPlan`: balance_cached, autodebet fields.
  - `PaymentInvoice`: va_code, amount, status (`menunggu`, `berhasil`, `gagal`, `kedaluwarsa`), source (`manual`, `autodebet`).
  - `PriceTier`: name, min_price, max_price, sort_order.
- Services:
  - `LedgerService`: satu-satunya pintu tulis perubahan saldo dengan `DB::transaction` dan `lockForUpdate` pada `SavingsPlan`. Nominal bigint integer rupiah tanpa desimal.
  - `InvoiceService`: pembuatan tagihan VA simulasi 16 digit berlaku 24 jam, callback simulasi bayar berhasil (mencatat ledger `setoran` via `LedgerService`) dan simulasi gagal/kedaluwarsa.
  - `PlanService`: pembuatan rencana tabungan, kalkulasi cicilan bulanan, penarikan dana (ledger `penarikan` via `LedgerService`), toggle autodebet.
- Controllers & UI Penabung:
  - `/rencana` (daftar rencana), `/rencana/buat` (form buat rencana dengan pilihan tier), `/rencana/{id}` (detail rencana, progress bar emas, riwayat transaksi ledger).
  - `/rencana/{id}/setor` (input nominal setoran atau pilih cicilan bulanan), `/tagihan/{id}` (halaman pembayaran simulasi VA dengan banner mode simulasi, tombol "Simulasikan Berhasil" dan "Simulasikan Gagal").
  - `/rencana/{id}/tarik` (tarik saldo dengan validasi saldo cukup).
  - Dashboard Beranda Penabung (`/beranda`): Hero saldo ala mobile banking (gradien green-900 ke green-600, toggle sembunyikan/tampilkan saldo, daftar transaksi dikelompokkan tanggal).
- Scheduler / Command:
  - `ExpireInvoices` (menit): tagihan `menunggu` lewat `expires_at` jadi `kedaluwarsa`.
  - `GenerateAutodebetInvoices` (harian 06:00): generate invoice bagi rencana yang autodebet-nya aktif dan tanggal jatuh tempo hari ini.
- Tests Fase 2:
  - Aturan bisnis BR-01 (append-only ledger, tolak update/delete).
  - Aturan bisnis BR-02 (bigint uang tanpa desimal).
  - Aturan bisnis BR-03 (LedgerService transaksi tunggal + concurrency lock testing di MySQL).
  - Invoices simulasi bayar berhasil menambah saldo, gagal/kedaluwarsa tidak menambah saldo.
  - Penarikan dana mengurangi saldo via LedgerService dan tolak saldo negatif.
