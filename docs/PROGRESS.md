# Progress Pengerjaan YuKurban (Web + PWA)

Sumber kebenaran tunggal: `docs/PRD.md`

## Status Fase

- [x] **Fase 1**: Inisialisasi proyek, autentikasi, peran, layout dan token desain, PWA dasar.
- [x] **Fase 2**: Rencana tabungan, tagihan simulasi, `LedgerService`, dashboard, tarik dana, autodebet.
- [x] **Fase 3**: Peternak dan hewan: profil, verifikasi, CRUD hewan + media, review admin, katalog.
- [x] **Fase 4**: Wilayah, zona, biaya, jendela musim, kuota (Filament / Admin Panel).
- [x] **Fase 5**: Pembelian: reservasi, rincian biaya, konfirmasi, `OrderService`, payout.
- [x] **Fase 6**: Status, bukti, timeline; dua alur metode.
- [x] **Fase 7**: Tiket tiga tipe, catatan pesanan, notifikasi, pengumuman.
- [ ] **Fase 8**: Laporan, audit log, seeder lengkap, polesan UI, Lighthouse, pengujian.

## Langkah Fase 7
- Migrasi: `tickets`, `ticket_replies`, `ticket_attachments`, `announcements`, `notifications`.
- Enums & Models:
  - `TicketType` (`pertanyaan_prabeli`, `laporan_masalah`, `bantuan_peternak`), `TicketStatus` (`dibuka`, `ditanggapi`, `diputuskan`, `selesai`, `ditolak`), `TicketDecision` (`ganti_hewan`, `dana_kembali`, `ditolak`).
  - `Ticket`, `TicketReply`, `TicketAttachment`, `Announcement` dengan scope audience (`semua`, `penabung`, `peternak`).
- Service `TicketService`:
  - Pembuatan tiket pra-beli terikat hewan kurban.
  - Pembuatan tiket bantuan peternak untuk kendala operasional/kemitraan.
  - Pembuatan tiket laporan masalah terikat pesanan dengan validasi kepemilikan dan batas waktu 48 jam pasca tiba/salur (BR-15).
  - Penandaan pesanan menjadi sengketa (`is_disputed = true`) untuk menunda auto-confirm.
  - Balasan tiket dengan opsi tandai FAQ publik tanpa identitas penanya.
  - Putusan admin untuk sengketa:
    - `dana_kembali`: refund penuh via `LedgerService` (pengembalian_dana, masuk, BR-01, BR-03), pembatalan payout (BR-13), dan pembatalan pesanan.
    - `ganti_hewan`: dicatat dalam putusan dan pelepasan dispute.
    - `ditolak`: pencatatan alasan penolakan dan pelepasan dispute.
  - Penutupan tiket (`closeTicket`).
- Filament Admin Panel:
  - `TicketResource` (`/admin/tickets`): tabel filter tipe & status, aksi balas tiket (dengan opsi FAQ publik), aksi putusan sengketa, aksi tutup tiket.
  - `AnnouncementResource` (`/admin/announcements`): CRUD pengumuman dengan segmentasi audiens (`semua`, `penabung`, `peternak`).
- UI & Routes:
  - Penabung: Daftar & Detail tiket (`/tiket`), formulir buka tiket baru (`/tiket/buat`), pengumuman resmi kurban (`/pengumuman`).
  - Peternak: Pusat bantuan peternak & riwayat tiket (`/peternak/bantuan`), pengumuman mitra (`/peternak/pengumuman`).
- Tests Fase 7 (`TicketServiceTest.php`):
  - Pembuatan tiket pra-beli dan bantuan peternak.
  - Validasi kepemilikan pesanan dan batas waktu lapor 48 jam (BR-15).
  - Balasan tiket, pembaruan status, dan penandaan FAQ publik.
  - Putusan sengketa dana kembali dengan pengembalian saldo via `LedgerService` dan pembatalan payout.
  - Scoping pengumuman berdasarkan target audiens.

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
