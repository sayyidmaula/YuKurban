# Progress Pengerjaan YuKurban (Web + PWA)

Sumber kebenaran tunggal: `docs/PRD.md`

## Status Fase

- [x] **Fase 1**: Inisialisasi proyek, autentikasi, peran, layout dan token desain, PWA dasar.
- [x] **Fase 2**: Rencana tabungan, tagihan simulasi, `LedgerService`, dashboard, tarik dana, autodebet.
- [x] **Fase 3**: Peternak dan hewan: profil, verifikasi, CRUD hewan + media, review admin, katalog.
- [x] **Fase 4**: Wilayah, zona, biaya, jendela musim, kuota (Filament / Admin Panel).
- [x] **Fase 5**: Pembelian: reservasi, rincian biaya, konfirmasi, `OrderService`, payout.
- [ ] **Fase 6**: Status, bukti, timeline; dua alur metode.
- [ ] **Fase 7**: Tiket tiga tipe, catatan pesanan, notifikasi, pengumuman.
- [ ] **Fase 8**: Laporan, audit log, seeder lengkap, polesan UI, Lighthouse, pengujian.

## Langkah Fase 5
- Migrasi: `orders`, `order_status_logs`, `payouts`.
- Enums: `OrderMethod` (`sembelih_tim`, `kirim_hidup`), `OrderStatus` (11 status sesuai PRD), `PayoutStatus` (`tertahan`, `dibayarkan`, `dibatalkan`).
- Service: `OrderService` (reservasi, perhitungan biaya, konfirmasi pesanan, pembatalan mandiri sebelum jadwal, kadaluwarsa reservasi).
- Controllers & UI:
  - Penabung: Beli/Reservasi hewan, Rincian biaya transparan (BR-09), Konfirmasi dengan persetujuan wakalah atau data penerima (BR-12), Pembatalan mandiri (BR-14), Daftar & Detail pesanan.
  - Peternak: Daftar pesanan dengan privasi ketat tanpa identitas pembeli (BR-10), Daftar dan ringkasan Payout status tertahan/dibayarkan (BR-13).
- Scheduled Task: `reservations:expire` dijalankan setiap 5 menit untuk melepaskan reservasi yang habis waktu.
- Tests Fase 5 (`OrderServiceTest.php`):
  - Validasi jendela pembelian per metode (BR-05).
  - Validasi dukungan zona layanan (BR-07).
  - Kalkulasi biaya & komisi platform (BR-09).
  - Penguncian hewan berstatus `dipesan` saat reservasi (BR-06).
  - Validasi saldo tabungan mencukupi saat reservasi.
  - Akad wakalah untuk sembelih tim & data penerima untuk kirim hidup (BR-12).
  - Pemotongan saldo tabungan lewat `LedgerService` & pembuatan escrow payout `tertahan` (BR-01, BR-03, BR-13).
  - Pembatalan mandiri sebelum jadwal dengan pengembalian dana penuh via `LedgerService` (BR-14).
  - Pelepasan hewan saat reservasi kedaluwarsa tanpa pemotongan saldo.
  - Privasi data pembeli di tampilan peternak (BR-10).

## Langkah Fase 4
- Migrasi: `service_zones`, `fee_rules`, `slaughter_quotas`.
- Model:
  - `ServiceZone`: region_id (unique FK), supports_sembelih_tim (bool), supports_kirim_hidup (bool), delivery_fee (bigint), is_active.
  - `FeeRule`: animal_type (unique), fee_slaughter (bigint), fee_distribution (bigint), commission_peternak (bigint).
  - `SlaughterQuota`: date (unique), quota (int).
- Admin Panel (`/admin`):
  - Ditema warna hijau-emas.
  - Manajemen Pengaturan Musim: jendela pembelian sembelih_tim & kirim_hidup, tanggal Idul Adha, umur minimum hewan.
  - Manajemen Biaya & Komisi per jenis hewan (`fee_rules`).
  - Manajemen Wilayah & Zona Layanan (`service_zones`, ongkir, metode didukung).
  - Manajemen Kuota Sembelih (`slaughter_quotas` per tanggal).
  - Verifikasi Peternak & Review Hewan Kurban.
- Business Rules:
  - BR-05 Jendela pembelian per metode.
  - BR-07 Zona: Metode tersedia hanya jika wilayah tujuan mendukungnya (`service_zones`).
  - BR-08 Kuota sembelih: Penjadwalan sembelih tim menolak tanggal yang kuotanya penuh (`slaughter_quotas`).
  - BR-09 Rincian biaya: Komisi platform nominal tetap per jenis hewan, dibebankan ke peternak (mengurangi payout).
- Tests Fase 4:
  - Validasi ketersediaan metode di zona layanan (BR-07).
  - Pengecekan kuota sembelih harian (BR-08).
  - Pengaturan biaya dan komisi per jenis hewan (BR-09).
  - Akses panel admin terbatas untuk role `admin`.
