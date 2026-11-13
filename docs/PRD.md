# PRD — YuKurban (Web + PWA)

Versi 1.0 · 11 November 2026 · Dokumen kerja untuk vibe coding · Proyek simulasi, bukan layanan keuangan sungguhan

## 1. Ringkasan dan Tujuan

YuKurban adalah web responsif sekaligus PWA yang mempertemukan tiga pihak: **penabung**, **peternak**, dan **tim platform (admin)**. Penabung menabung dulu tanpa terikat hewan tertentu. Ketika saldo cukup dan jendela pembelian terbuka, penabung memilih hewan nyata dari katalog dengan harga hari itu, lalu memilih salah satu metode:

1. **Sembelih tim** — tim kami menyembelih atas nama penabung dan menyalurkan dagingnya.
2. **Kirim hewan** — hewan hidup dikirim ke daerah tujuan untuk disembelih sendiri oleh penerima.

Semua komunikasi pembeli dan peternak lewat platform. Pembayaran dan pencairan dana adalah **simulasi**.

**Nilai utama:** amanah dan transparansi. Ledger tidak bisa diubah, rincian biaya selalu tampil, ada bukti di tiap tahap, dan dana tabungan tidak dipakai operasional.

**Tujuan produk**

- Penabung bisa merencanakan, menabung, dan membeli dengan alur yang jelas.
- Peternak kecil bisa menjual lewat katalog yang terverifikasi.
- Setiap rupiah dan setiap perubahan status bisa ditelusuri.
- Tampilan modern ala mobile banking, nyaman di HP, bisa dipasang sebagai aplikasi (PWA).

## 2. Ruang Lingkup

| Tahap | Isi |
| --- | --- |
| **MVP** | Autentikasi 3 peran; rencana tabungan; setor simulasi + ledger; autodebet simulasi; tarik dana; katalog hewan + review admin; wilayah dan zona; pembelian 2 metode; status + bukti; payout peternak simulasi; tiket (3 tipe); notifikasi dalam aplikasi; laporan admin; PWA; seeder data demo |
| **Tahap 2** | Patungan sapi 1/7; tawar-menawar; chat; email/WhatsApp; pelacakan kurir; payment gateway sungguhan; data wilayah lengkap; akikah |
| **Tidak dikerjakan** | Haji/umrah, kendaraan, bunga atau bagi hasil atas dana tabungan, GPS |

## 3. Peran dan Hak Akses

| Peran | Bisa | Tidak bisa |
| --- | --- | --- |
| **Penabung** | Rencana, setor, tarik, lihat katalog, beli, konfirmasi, tiket, notifikasi | Melihat kontak peternak; mengubah ledger |
| **Peternak** | Profil, kelola hewan, lihat pesanan (tanpa identitas pembeli), tandai siap diserahkan, lihat payout, tiket bantuan | Melihat nama/kontak/alamat pembeli; mengubah harga hewan yang sedang direservasi |
| **Admin** | Verifikasi peternak dan hewan, atur pengaturan musim/biaya/zona/kuota, jadwalkan dan perbarui status pesanan, unggah bukti, putuskan tiket, lihat laporan | Mengubah atau menghapus ledger |
| **Tamu** | Lihat landing, FAQ publik, katalog (mode pratinjau), form Hubungi kami | Beli, bertanya per hewan, lapor masalah |

Satu tabel `users` dengan kolom `role`. Admin dibuat lewat seeder, tidak ada halaman daftar admin.

## 4. Alur Utama

**A. Tabungan.** Daftar, lalu buat rencana (nama, tingkat harga, tahun target, cicilan per bulan). Setor: sistem membuat tagihan virtual account simulasi berlaku 24 jam; pengguna menekan Simulasikan berhasil atau gagal. Jika berhasil, satu baris ledger `setoran` ditambahkan dan saldo naik. Autodebet simulasi membuat tagihan otomatis tiap bulan. Dana bisa ditarik kapan saja (ledger `penarikan`).

**B. Beli.** Katalog selalu bisa dilihat. Tombol Beli aktif hanya jika: jendela metode terbuka, saldo cukup untuk total, hewan berstatus `tayang`, dan daerah tujuan didukung metode itu. Langkah: pilih hewan, pilih daerah tujuan dan metode, lihat rincian biaya, tekan Beli. Hewan direservasi 24 jam. Pembeli mengonfirmasi (centang persetujuan kuasa untuk sembelih tim; isi data penerima untuk kirim hewan). Saat konfirmasi: ledger `pembelian` keluar, pesanan `dikonfirmasi`, payout peternak berstatus `tertahan`. Jika reservasi lewat waktu, hewan tayang lagi dan tidak ada potongan saldo.

**C. Sembelih tim.** `dikonfirmasi` → `dijadwalkan` (admin pilih tanggal, cek kuota) → `disembelih` (admin unggah foto/video) → `disalurkan` (laporan penyaluran dan catatan) → penabung konfirmasi → `selesai` (payout dibayarkan simulasi). Jika tidak ada respons penabung dan tidak ada tiket terbuka, otomatis `selesai` setelah batas waktu.

**D. Kirim hewan.** `dikonfirmasi` → `dijadwalkan_kirim` → `dalam_perjalanan` (foto kondisi saat berangkat) → `diterima` (foto saat tiba) → penabung konfirmasi → `selesai`.

**E. Tiket.** Tiga tipe dalam satu modul: `pertanyaan_prabeli` (penabung ke admin dari halaman hewan), `laporan_masalah` (terikat satu pesanan), `bantuan_peternak`. Status: `dibuka` → `ditanggapi` → `diputuskan` → `selesai`. Keputusan admin untuk laporan masalah: `ganti_hewan`, `dana_kembali` (ledger `pengembalian_dana`, payout dibatalkan), atau `ditolak` dengan alasan. Balasan yang layak boleh ditandai FAQ publik tanpa identitas penanya.

**F. Verifikasi.** Peternak daftar, status `menunggu`; admin memeriksa lalu `terverifikasi` atau `ditolak` dengan alasan. Hewan baru berstatus `menunggu_review`; admin memeriksa kelengkapan lalu `tayang`.

## 5. Aturan Bisnis

- **BR-01 Ledger append-only.** Tidak ada update atau delete pada `ledger_entries`. Koreksi dibuat sebagai baris baru. Saldo adalah jumlah ledger; `balance_cached` pada rencana hanya cache yang diperbarui dalam transaksi yang sama.
- **BR-02 Uang bilangan bulat.** Semua nominal rupiah disimpan `bigint` tanpa desimal.
- **BR-03 Satu pintu tulis.** Semua perubahan saldo lewat `LedgerService` di dalam `DB::transaction` dengan `lockForUpdate` pada rencana.
- **BR-04 Rencana tidak terikat hewan.** Rencana hanya punya target dan tingkat harga. Hewan dipilih saat membeli.
- **BR-05 Jendela pembelian per metode.** Diatur admin di `settings`. Di luar jendela, tombol Beli nonaktif, katalog tampil dengan label harga perkiraan dan tidak mengikat.
- **BR-06 Reservasi.** Pilih hewan memasang `reserved_until` (default 24 jam). Hewan yang direservasi tidak bisa dipesan orang lain. Job menyelesaikan reservasi yang kedaluwarsa.
- **BR-07 Zona.** Metode tersedia hanya jika wilayah tujuan mendukungnya (`service_zones`).
- **BR-08 Kuota sembelih.** Penjadwalan sembelih tim menolak tanggal yang kuotanya penuh (`slaughter_quotas`).
- **BR-09 Rincian biaya.** Total pembeli sembelih tim = harga hewan + ujrah sembelih + ujrah distribusi. Total kirim hewan = harga hewan + ongkir zona. Rincian tampil sebelum konfirmasi. Komisi platform nominal tetap per jenis hewan, dibebankan ke peternak (mengurangi payout) dan terlihat di halaman payout peternak serta laporan admin. Tidak ada biaya tersembunyi.
- **BR-10 Privasi.** Peternak tidak pernah melihat nama, kontak, atau alamat pembeli. Peternak melihat nomor pesanan, hewan, dan jadwal ambil.
- **BR-11 Tanpa kontak langsung.** Tidak ada fitur pesan langsung pembeli dan peternak. Semua lewat tiket dan catatan pesanan (antara admin dan peternak).
- **BR-12 Persetujuan kuasa.** Metode sembelih tim wajib mencentang persetujuan kuasa (wakalah); waktunya disimpan di `wakalah_agreed_at`.
- **BR-13 Payout.** Dibuat `tertahan` saat pesanan dikonfirmasi; berubah `dibayarkan` saat pesanan `selesai`; `dibatalkan` jika dana kembali.
- **BR-14 Pembatalan.** Sebelum `dijadwalkan` atau `dijadwalkan_kirim`, penabung boleh membatalkan dan dana kembali penuh ke rencana. Setelahnya lewat tiket.
- **BR-15 Batas lapor.** Laporan masalah hanya bisa dibuat dalam `report_deadline_hours` (default 48 jam) setelah status `disalurkan` atau `diterima`.
- **BR-16 Kualitas data hewan.** Wajib: jenis, umur (bulan), bobot, catatan kesehatan, harga, minimal 3 foto, 1 video, wilayah. Validasi umur minimum per jenis mengambil dari `settings.min_age_months` yang diisi admin dari rujukan resmi (jangan di-hardcode).
- **BR-17 Mode simulasi.** Banner MODE SIMULASI tampil jelas di halaman pembayaran, pencairan, dan footer.
- **BR-18 Tanggal Idul Adha.** Diisi admin di `settings` (bisa berbeda satu hari tergantung penetapan). Dipakai untuk pengingat dan hitungan mundur, bukan untuk menentukan akad.

## 6. Tech Stack dan Arsitektur

- **Backend:** Laravel (versi stabil terbaru), PHP, MySQL 8.
- **Frontend penabung dan peternak:** Laravel Breeze stack React (Inertia.js), Tailwind CSS, komponen gaya shadcn/ui (Radix), `lucide-react` untuk ikon, `recharts` untuk grafik, `framer-motion` opsional.
- **Panel admin:** Filament, ditema warna hijau-emas, rute `/admin`.
- **PWA:** `vite-plugin-pwa`.
- **Penyimpanan file:** disk lokal (`storage:link`), validasi tipe dan ukuran.
- **Queue/Scheduler:** queue driver `database`, scheduler Laravel.
- **Test:** Pest atau PHPUnit untuk `LedgerService` dan transisi status pesanan.
- Cek dokumentasi resmi untuk versi dan perintah instalasi terbaru; jangan menebak versi.

**Struktur kode**

- `app/Services/`: `LedgerService`, `PlanService`, `InvoiceService`, `OrderService` (state machine), `TicketService`.
- `app/Enums/`: `Role`, `OrderStatus`, `OrderMethod`, `AnimalType`, `AnimalStatus`, `TicketType`, `TicketStatus`, `LedgerType`.
- `app/Policies/`: akses per peran. `routes/console.php`: job terjadwal.
- `resources/js/Pages/{Penabung,Peternak,Public}`; `resources/js/Components/ui`; `resources/js/Layouts`.

## 7. Skema Database

Semua tabel punya `id`, `created_at`, `updated_at` kecuali dinyatakan lain. Nominal = `bigint` rupiah. Gunakan foreign key dan indeks pada kolom yang sering difilter.

**users** — name, email (unique), password (hash), role (`penabung`|`peternak`|`admin`), phone (nullable), is_active (bool, default true), email_verified_at, remember_token.

**regions** — province, city (unique bersama province), is_active (bool).

**service_zones** — region_id (unique FK), supports_sembelih_tim (bool), supports_kirim_hidup (bool), delivery_fee (bigint), is_active.

**peternak_profiles** — user_id (unique FK), farm_name, address, region_id (FK), description, farm_photo_path, status (`menunggu`|`terverifikasi`|`ditolak`|`nonaktif`), rejection_reason, verified_by (FK users), verified_at.

**price_tiers** — name (`ekonomis`|`standar`|`premium`), min_price, max_price (bigint), sort_order.

**fee_rules** — animal_type (unique), fee_slaughter, fee_distribution, commission_peternak (semua bigint).

**animals** — peternak_profile_id (FK), type (`kambing`|`domba`|`sapi`), title, age_months (int), weight_kg (decimal 6,1), health_notes (text), price (bigint), region_id (FK), status (`draft`|`menunggu_review`|`tayang`|`dipesan`|`terjual`|`ditarik`), reserved_by (FK users, nullable), reserved_until (datetime, nullable), review_note, reviewed_by, reviewed_at. Satu baris = satu hewan fisik.

**animal_media** — animal_id (FK), type (`foto`|`video`), path, caption, sort_order.

**savings_plans** — user_id (FK), name, tier_id (FK nullable), target_amount, target_year (int), monthly_amount, status (`menabung`|`dibeli`|`ditutup`), autodebet_enabled (bool), autodebet_day (tinyint nullable), balance_cached (bigint, default 0).

**payment_invoices** (tagihan simulasi) — user_id, savings_plan_id, amount, va_code (string 16, unique), status (`menunggu`|`berhasil`|`gagal`|`kedaluwarsa`), expires_at, paid_at, source (`manual`|`autodebet`).

**ledger_entries** — tanpa updated_at. savings_plan_id (FK), user_id (FK), type (`setoran`|`pembelian`|`pengembalian_dana`|`penarikan`|`koreksi`), direction (`masuk`|`keluar`), amount (bigint positif), balance_after (bigint), reference_type, reference_id (nullable), description, created_by (FK). Indeks (savings_plan_id, created_at). Model menolak update dan delete.

**orders** — order_no (unique), user_id, savings_plan_id, animal_id, region_id (daerah tujuan), method (`sembelih_tim`|`kirim_hidup`), status (lihat bawah), animal_price, fee_slaughter, fee_distribution, fee_delivery, commission, total_buyer_pays (semua bigint), reserved_until, wakalah_agreed_at (nullable), recipient_name, recipient_phone, recipient_address (untuk kirim_hidup), distribution_note (catatan pembagian daging, text), peternak_ready_at (nullable), scheduled_at, slaughtered_at, delivered_at, confirmed_at, auto_confirm_at, is_disputed (bool).

- Status umum: `direservasi`, `dikonfirmasi`, `selesai`, `kedaluwarsa`, `dibatalkan`.
- Sembelih tim: `dijadwalkan`, `disembelih`, `disalurkan`.
- Kirim hidup: `dijadwalkan_kirim`, `dalam_perjalanan`, `diterima`.
- Transisi hanya lewat `OrderService`; tolak transisi yang tidak valid.

**order_status_logs** — order_id, from_status, to_status, actor_id, note, created_at.

**order_evidences** — order_id, stage (`berangkat`|`tiba`|`sembelih`|`penyaluran`), type (`foto`|`video`|`dokumen`), path, uploaded_by.

**order_notes** — order_id, user_id, body. Hanya terlihat admin dan peternak terkait.

**payouts** — order_id (unique), peternak_profile_id, amount (harga hewan dikurangi komisi), commission, status (`tertahan`|`dibayarkan`|`dibatalkan`), released_at.

**tickets** — type (`pertanyaan_prabeli`|`laporan_masalah`|`bantuan_peternak`), creator_id, order_id (nullable), animal_id (nullable), category, subject, status (`dibuka`|`ditanggapi`|`diputuskan`|`selesai`|`ditolak`), decision (`ganti_hewan`|`dana_kembali`|`ditolak`, nullable), decision_note, decided_by, decided_at.

**ticket_replies** — ticket_id, user_id, body, is_faq_public (bool), is_hidden (bool).

**ticket_attachments** — ticket_id, reply_id (nullable), path, type.

**announcements** — title, body, audience (`semua`|`penabung`|`peternak`), created_by, published_at.

**contact_messages** (tamu) — name, email, message, status (`baru`|`dibaca`).

**slaughter_quotas** — date (unique), quota (int).

**settings** — key (unique), value (json). Kunci: `purchase_window_sembelih_tim` {open_at, close_at}, `purchase_window_kirim_hidup` {open_at, close_at}, `idul_adha_date`, `reservation_hours` (24), `invoice_expiry_hours` (24), `report_deadline_hours` (48), `auto_confirm_hours` (48), `min_age_months` {kambing, domba, sapi}.

**audit_logs** — actor_id, action, subject_type, subject_id, meta (json), created_at.

Tabel `notifications` memakai bawaan Laravel (database notifications).

**Relasi inti:** user 1—n savings_plans; savings_plan 1—n ledger_entries, payment_invoices; peternak_profile 1—n animals; animal 1—n animal_media; user 1—n orders; order n—1 animal, savings_plan; order 1—1 payout; order 1—n order_status_logs, order_evidences, order_notes; ticket 1—n ticket_replies.

## 8. Halaman dan Rute

**Publik:** `/` landing, `/masuk`, `/daftar`, `/katalog` (pratinjau), `/katalog/{id}`, `/faq`, `/ketentuan`, `/hubungi`.

**Penabung:** `/beranda`, `/rencana`, `/rencana/buat`, `/rencana/{id}`, `/rencana/{id}/setor`, `/tagihan/{id}` (halaman bayar simulasi), `/rencana/{id}/tarik`, `/katalog` (mode aktif), `/beli/{animal}` (rincian dan konfirmasi), `/pesanan`, `/pesanan/{id}` (timeline dan bukti), `/tiket`, `/tiket/buat`, `/tiket/{id}`, `/notifikasi`, `/akun`.

**Peternak:** `/peternak/beranda`, `/peternak/profil`, `/peternak/hewan`, `/peternak/hewan/buat`, `/peternak/hewan/{id}/edit`, `/peternak/pesanan`, `/peternak/pesanan/{id}` (tanpa identitas pembeli, ada catatan dengan admin), `/peternak/pembayaran`, `/peternak/bantuan`, `/peternak/pengumuman`.

**Admin (Filament `/admin`):** Dashboard ringkasan; Peternak (verifikasi); Hewan (review); Pesanan (jadwal, status, bukti, laporan penyaluran); Tiket (balas dan putuskan); Pembayaran/Payout; Ledger (hanya baca); Pengaturan Musim (jendela, Idul Adha, umur minimum); Biaya dan Zona; Kuota Sembelih; Pengumuman; Pesan Tamu; Laporan transparan (ekspor CSV); Audit log.

## 9. Desain UI/UX

**Arah visual:** modern, bersih, bernuansa mobile banking. Referensi pola: BYOND by BSI (superapp dengan fitur Goal Setting untuk target tabungan, notifikasi anggaran, kalkulator zakat dan waktu salat) dan BRImo. Ambil polanya, bukan aset atau logonya. Lihat langsung di aplikasinya untuk inspirasi tata letak.

**Token warna (Tailwind `theme.extend.colors`)**

| Token | Hex | Pakai untuk |
| --- | --- | --- |
| green-900 | #0B3B2C | Latar hero, header gelap |
| green-700 | #0F5A3F | Hover, tombol sekunder gelap |
| green-600 | #14704D | **Primer**: tombol, tautan aktif, theme-color PWA |
| green-500 | #1E8A5E | Sukses, progres |
| green-100 / 50 | #DDF0E6 / #F1F9F5 | Chip, latar lembut |
| gold-600 | #B68A1B | Ikon dan garis aksen |
| gold-500 | #C9A227 | **Aksen**: badge, highlight, cincin progres |
| gold-300 / 100 | #E3CB76 / #F7EFD0 | Latar lembut aksen |
| surface | #FAFAF6 | Latar halaman (putih hangat) |
| white | #FFFFFF | Kartu |
| border | #E6E4D9 | Garis |
| text / muted | #10211A / #5E6E66 | Teks utama / sekunder |
| danger / info | #C0392B / #2F6F9F | Error / informasi |

Aturan kontras: teks emas di atas putih tidak lolos kontras. Emas dipakai untuk ikon, garis, badge, dan teks hanya di atas hijau gelap. Teks isi minimal rasio 4.5:1.

**Tipografi:** Plus Jakarta Sans (fallback Inter, system-ui). Angka uang `tabular-nums`, format Rp 1.250.000. Skala: 12 / 14 / 16 / 20 / 24 / 32.

**Bentuk:** radius kartu 16px, hero 24px, tombol 12px; bayangan lembut; target sentuh minimal 44px.

**Pola komponen (ala mobile banking)**

- **Hero kartu saldo** di Beranda: gradien green-900 ke green-600, garis aksen emas tipis, pola geometris Islami transparan (opacity sekitar 6%), tombol sembunyikan/tampilkan saldo (ikon mata).
- **Aksi cepat** grid 4 ikon: Setor, Katalog, Riwayat, Bantuan.
- **Kartu rencana** dengan progress ring/bar emas, persen, sisa bulan, estimasi tercapai.
- **Bottom tab bar** (mobile): Beranda, Rencana, Katalog, Pesanan, Akun (peternak: Beranda, Hewan, Pesanan, Pembayaran, Akun). Desktop: sidebar kiri.
- **Bottom sheet** untuk konfirmasi (rincian biaya, persetujuan wakalah) dan filter katalog.
- **Layar sukses** setelah setor/beli: centang animasi dan ringkasan bergaya struk.
- **Daftar transaksi** dikelompokkan per tanggal, ikon per tipe (masuk hijau, keluar netral).
- **Timeline status pesanan** vertikal dengan bukti foto/video per tahap.
- **Kartu hewan** katalog: foto besar, jenis, umur, bobot, harga, badge tingkat, nama kandang dan daerah (tanpa kontak).
- Skeleton loader, empty state ramah dengan ilustrasi sederhana, toast singkat.
- Banner MODE SIMULASI (pita emas tipis) di halaman pembayaran dan footer.
- Gerak 150–250 ms, hormati `prefers-reduced-motion`.

**Responsif:** mobile-first, baseline 360px; breakpoint `md` 768px dan `lg` 1024px. Mobile memakai bottom tab, tablet dan desktop memakai sidebar dan grid dua sampai tiga kolom. Tabel lebar scroll horizontal di dalam kontainernya, bukan seluruh halaman.

## 10. PWA

- `manifest.webmanifest`: name YuKurban, short_name YuKurban, start_url `/beranda`, display `standalone`, theme_color `#14704D`, background_color `#FAFAF6`, ikon 192 dan 512 plus versi maskable.
- Service worker via `vite-plugin-pwa` (autoUpdate): precache aset build, cache gambar katalog dengan stale-while-revalidate, `offline.html` sebagai fallback.
- Halaman dinamis dan semua transaksi **network-only**; saat offline tampil pesan jelas, tanpa antrean transaksi.
- Tombol Pasang aplikasi di halaman Akun (tangani `beforeinstallprompt`); untuk iOS tampilkan petunjuk Tambah ke Layar Utama.
- Wajib HTTPS saat deploy (localhost boleh saat pengembangan). Uji dengan Lighthouse (kategori PWA dan Performance).

## 11. Job Terjadwal

| Job | Jadwal | Tugas |
| --- | --- | --- |
| ExpireInvoices | tiap menit | `menunggu` melewati `expires_at` jadi `kedaluwarsa` |
| ExpireReservations | tiap 5 menit | Lepas reservasi hewan dan tandai pesanan `kedaluwarsa` |
| GenerateAutodebetInvoices | harian 06.00 | Buat tagihan bagi rencana autodebet yang jatuh tempo |
| AutoConfirmOrders | tiap jam | Selesaikan pesanan melewati `auto_confirm_at` tanpa tiket terbuka |
| SendReminders | harian | Pengingat jatuh tempo, jendela pembelian segera dibuka, hitung mundur Idul Adha |

## 12. Data Demo (Seeder)

- 1 admin; 3 peternak (1 `menunggu`, 1 `ditolak`, 1 `terverifikasi`) dan 5 penabung. Password dev `password` (hanya lokal).
- 5 wilayah demo (misalnya Bandung, Garut, Tasikmalaya, Yogyakarta, Makassar) dengan zona berbeda: ada yang mendukung kedua metode, ada yang satu metode.
- 3 tingkat harga, fee_rules untuk 3 jenis hewan, kuota sembelih beberapa tanggal.
- Sekitar 12 hewan dengan status campuran (`tayang`, `menunggu_review`, `dipesan`).
- Rencana dengan ledger realistis (beberapa setoran), pesanan di berbagai status, dan tiket di berbagai status.
- Semua angka adalah data contoh, bukan harga pasar.

## 13. Kriteria Penerimaan dan Skenario Uji

**Tabungan**

- Setor berhasil menambah satu baris ledger dan saldo sama dengan jumlah ledger.
- Tagihan kedaluwarsa tidak mengubah saldo.
- Dua setoran bersamaan tidak membuat saldo salah (uji dengan lock).

**Pembelian**

- Tombol Beli nonaktif jika di luar jendela, saldo kurang, atau wilayah tidak mendukung metode.
- Hewan direservasi tidak bisa dibeli orang lain; reservasi kedaluwarsa tidak memotong saldo.
- Rincian biaya sebelum konfirmasi sama dengan jumlah yang dipotong.

**Pesanan**

- Transisi status di luar aturan ditolak.
- Setiap transisi tercatat di `order_status_logs`; setiap tahap bukti bisa dilihat penabung.
- Peternak tidak bisa melihat data identitas pembeli (uji di respons Inertia dan di database query).

**Tiket**

- Laporan hanya bisa dibuat untuk pesanan milik sendiri dan dalam batas waktu.
- Keputusan `dana_kembali` menambah baris ledger `pengembalian_dana` dan membatalkan payout.

**Lainnya**

- Penabung tidak bisa membuka `/peternak/*` atau `/admin`; peternak tidak bisa membuka halaman penabung.
- PWA bisa dipasang; halaman offline tampil saat tanpa koneksi.
- Halaman utama nyaman di lebar 360px tanpa scroll horizontal.

## 14. Rencana Build dan Panduan Vibe Coding

**Urutan fase**

1. Inisialisasi proyek, autentikasi, peran, layout dan token desain, PWA dasar.
2. Rencana tabungan, tagihan simulasi, `LedgerService`, dashboard, tarik dana, autodebet.
3. Peternak dan hewan: profil, verifikasi, CRUD hewan + media, review admin, katalog.
4. Wilayah, zona, biaya, jendela musim, kuota (Filament).
5. Pembelian: reservasi, rincian biaya, konfirmasi, `OrderService`, payout.
6. Status, bukti, timeline; dua alur metode.
7. Tiket tiga tipe, catatan pesanan, notifikasi, pengumuman.
8. Laporan, audit log, seeder lengkap, polesan UI, Lighthouse, pengujian.

**Cara memakai PRD ini saat vibe coding**

- Tempel PRD ini sebagai konteks tetap, lalu minta satu fase per sesi dengan perintah seperti: kerjakan fase 2 sesuai PRD, tulis test untuk LedgerService, jangan ubah tabel di luar fase ini.
- Minta migrasi, model, service, dan test untuk satu fitur sekaligus, lalu jalankan test sebelum lanjut.
- Aturan keras untuk AI: tidak boleh ada update atau delete pada ledger; nominal selalu integer; semua perubahan saldo lewat `LedgerService`; peternak tidak boleh menerima data identitas pembeli.
- Commit kecil per fitur di Git.
- Setelah tiap fase, cek tampilan di lebar 360px dan 1280px.

## 15. Catatan Syariat dan Batasan

- Rancangan memisahkan **tabungan** dari **akad jual beli**: akad terjadi saat membeli hewan yang nyata dengan harga hari itu, bukan saat mulai menabung. Pertimbangan ini mengacu pada pembahasan skripsi tentang tabungan kurban dan unsur gharar, serta fatwa DSN-MUI tentang akad jual beli (No. 110/2017), wakalah bil ujrah (No. 113/2017), marketplace (No. 144/2021), dan samsarah (No. 151/2022). Periksa teks asli fatwa dan konsultasikan ke dosen atau ustaz; jangan menulis sistem ini sebagai pasti sesuai syariat.
- Pendapatan platform berupa ujrah dan komisi yang jelas dan diumumkan di awal. Bagian hewan tidak boleh dipakai sebagai upah penyembelih.
- Dana tabungan adalah amanah dan tidak dipakai untuk operasional atau diputar.
- Ketentuan umur hewan, kondisi, waktu penyembelihan, dan penyaluran daging dirujuk dari sumber resmi, bukan diputuskan oleh sistem.
- Proyek ini simulasi. Jika menjadi usaha sungguhan, perlu izin usaha dan kepatuhan pada ketentuan penyembelihan dan perlindungan konsumen dari pihak berwenang.

## 16. Asumsi dan Keputusan Terbuka

| Hal | Asumsi dalam PRD | Perlu dikonfirmasi |
| --- | --- | --- |
| Penarikan dana | Boleh kapan saja | Ya |
| Batas lapor masalah | 48 jam | Ya |
| Panjang jendela pembelian | Diatur admin; saran awal sekitar 30 hari sebelum Idul Adha, kirim hewan lebih awal | Ya |
| Pembagian daging | Cukup kolom catatan teks | Tidak mendesak |
| Kanal darurat musim kurban | Tiket dalam aplikasi; WhatsApp opsional | Ya |
| Pengiriman hewan | Dilakukan armada atau mitra tim kami | Ya |
| Angka biaya | Contoh simulasi | Ganti dengan angka lapangan |

