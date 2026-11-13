# Progress Pengerjaan YuKurban (Web + PWA)

Sumber kebenaran tunggal: `docs/PRD.md`

## Status Fase

- [x] **Fase 1**: Inisialisasi proyek, autentikasi, peran, layout dan token desain, PWA dasar.
- [x] **Fase 2**: Rencana tabungan, tagihan simulasi, `LedgerService`, dashboard, tarik dana, autodebet.
- [~] **Fase 3**: Peternak dan hewan: profil, verifikasi, CRUD hewan + media, review admin, katalog.
- [ ] **Fase 4**: Wilayah, zona, biaya, jendela musim, kuota (Filament).
- [ ] **Fase 5**: Pembelian: reservasi, rincian biaya, konfirmasi, `OrderService`, payout.
- [ ] **Fase 6**: Status, bukti, timeline; dua alur metode.
- [ ] **Fase 7**: Tiket tiga tipe, catatan pesanan, notifikasi, pengumuman.
- [ ] **Fase 8**: Laporan, audit log, seeder lengkap, polesan UI, Lighthouse, pengujian.

## Langkah Fase 3
- Migrasi: `regions`, `peternak_profiles`, `animals`, `animal_media`.
- Enums: `AnimalType` (`kambing`, `domba`, `sapi`), `AnimalStatus` (`draft`, `menunggu_review`, `tayang`, `dipesan`, `terjual`, `ditarik`).
- Model:
  - `Region`: province, city, is_active.
  - `PeternakProfile`: user_id, farm_name, address, region_id, description, farm_photo_path, status (`menunggu`, `terverifikasi`, `ditolak`, `nonaktif`), rejection_reason, verified_by, verified_at.
  - `Animal`: peternak_profile_id, type, title, age_months, weight_kg, health_notes, price, region_id, status, reserved_by, reserved_until, review_note, reviewed_by, reviewed_at. (Satu baris = satu hewan fisik).
  - `AnimalMedia`: animal_id, type (`foto`, `video`), path, caption, sort_order.
- Validasi data hewan (BR-16): jenis, umur (bulan), bobot, catatan kesehatan, harga, minimal 3 foto, 1 video, wilayah.
- Peternak UI:
  - `/peternak/profil` (lengkapi profil kandang, submit verifikasi).
  - `/peternak/hewan` (daftar hewan milik peternak), `/peternak/hewan/buat` (tambah hewan baru + upload foto/video), `/peternak/hewan/{id}/edit` (edit hewan status draft/menunggu_review).
- Admin Review:
  - Verifikasi peternak (terverifikasi / ditolak dengan alasan).
  - Review hewan kurban (tayang / revisi catatan).
- Publik & Penabung Katalog:
  - `/katalog` (katalog hewan dengan filter jenis, tingkat harga, wilayah, sort harga; mode pratinjau untuk tamu, mode aktif untuk penabung).
  - `/katalog/{id}` (detail hewan: galeri foto, video, info umur/bobot/kesehatan/kandang/daerah tanpa kontak langsung).
- Tests Fase 3:
  - Profil peternak & status verifikasi.
  - CRUD hewan kurban & validasi kelengkapan BR-16.
  - Penolakan akses peternak lain terhadap hewan yang bukan miliknya.
  - Katalog menampilkan hanya hewan `tayang`.
  - BR-10: Katalog dan detail hewan tidak menampilkan kontak/alamat pribadi peternak kepada pembeli.
