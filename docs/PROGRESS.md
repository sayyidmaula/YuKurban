# Progress Pengerjaan YuKurban (Web + PWA)

Sumber kebenaran tunggal: `docs/PRD.md`

## Status Fase

- [x] **Fase 1**: Inisialisasi proyek, autentikasi, peran, layout dan token desain, PWA dasar.
- [x] **Fase 2**: Rencana tabungan, tagihan simulasi, `LedgerService`, dashboard, tarik dana, autodebet.
- [x] **Fase 3**: Peternak dan hewan: profil, verifikasi, CRUD hewan + media, review admin, katalog.
- [x] **Fase 4**: Wilayah, zona, biaya, jendela musim, kuota (Filament / Admin Panel).
- [x] **Fase 5**: Pembelian: reservasi, rincian biaya, konfirmasi, `OrderService`, payout.
- [x] **Fase 6**: Status, bukti, timeline; dua alur metode.
- [ ] **Fase 7**: Tiket tiga tipe, catatan pesanan, notifikasi, pengumuman.
- [ ] **Fase 8**: Laporan, audit log, seeder lengkap, polesan UI, Lighthouse, pengujian.

## Langkah Fase 6
- Migrasi: `order_evidences` (stage, type, path, uploaded_by), `order_notes` (user_id, body).
- Models: `OrderEvidence`, `OrderNote`, dengan relasi ke `Order` dan `User`.
- State Machine & Workflow di `OrderService`:
  - **Sembelih Tim**: dikonfirmasi → dijadwalkan (validasi kuota harian BR-08) → disembelih → disalurkan (set `auto_confirm_at` 48 jam) → penabung konfirmasi / auto-confirm → selesai (rilis payout status `dibayarkan` BR-13, hewan menjadi `terjual`).
  - **Kirim Hidup**: dikonfirmasi → dijadwalkan_kirim → dalam_perjalanan → diterima (set `auto_confirm_at` 48 jam) → penabung konfirmasi / auto-confirm → selesai (rilis payout status `dibayarkan` BR-13, hewan menjadi `terjual`).
  - Tolak transisi yang tidak valid.
  - Aksi peternak: `markPeternakReady` menandai kesiapan hewan di kandang.
  - Upload bukti (`addEvidence`) dan catatan internal (`addNote`).
  - Scheduled command `orders:auto-confirm` (dijalankan setiap 15 menit) untuk menyelesaikan pesanan yang melewati batas waktu konfirmasi tanpa dispute.
- Admin Panel Filament:
  - Resource `OrderResource` (`/admin/orders`) dengan tabel interaktif, filter metode & status, dan modal aksi alur: jadwalkan sembelih, tandai disembelih, tandai disalurkan, jadwalkan kirim, dalam perjalanan, diterima, selesaikan pesanan & rilis payout, unggah bukti dokumentasi, dan tambah catatan internal.
- UI Penabung:
  - Timeline interaktif perjalanan kurban sesuai metode (`sembelih_tim` atau `kirim_hidup`).
  - Galeri dokumentasi & bukti pelaksanaan (`order_evidences`).
  - Aksi konfirmasi pesanan selesai bagi penabung saat status `disalurkan` / `diterima`.
- UI Peternak:
  - Tombol aksi "Tandai Hewan Siap di Kandang" beserta waktu kesiapan kandang.
- Tests Fase 6 (`OrderWorkflowTest.php`):
  - Alur lengkap Sembelih Tim hingga selesai dan rilis payout.
  - Pengecekan penolakan penjadwalan saat kuota sembelih penuh (BR-08).
  - Alur lengkap Kirim Hidup hingga selesai dan rilis payout.
  - Penolakan transisi status tidak valid.
  - Pencatatan kesiapan hewan oleh peternak (`peternak_ready_at`).
  - Pengunggahan bukti dokumentasi dan catatan internal.
  - Otomatisasi konfirmasi selesai lewat job terjadwal.
  - Pencegahan auto-confirm untuk pesanan yang bersengketa (`is_disputed = true`).

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
