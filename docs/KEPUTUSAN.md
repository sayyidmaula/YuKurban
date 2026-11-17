# Catatan Keputusan Teknis & Penyesuaian

Dokumen ini mencatat keputusan arsitektural dan penyesuaian terhadap PRD, framework versi terkini (Laravel 13, Vite 8), serta lingkungan eksekusi.

## 1. Lingkungan Eksekusi & Sandbox (Windows)
- Pada lingkungan Windows sandbox, fungsi bawaan PHP `is_readable()` mengembalikan `false` akibat penolakan evaluasi ACL oleh restricted token Windows API, sedangkan `is_file()` dan `fopen()` / `file_get_contents()` dapat membaca file dengan baik.
- `vendor/phpunit/phpunit/src/TextUI/Configuration/BootstrapLoader.php` disesuaikan menggunakan `is_file($filename)` agar eksekusi test runner PHPUnit 12 dapat memuat bootstrap autoload composer.

## 2. Pengaturan Timezone & Database Test
- Timezone aplikasi diatur ke `Asia/Jakarta` di `config/app.php`.
- Database testing `yukurban_test` disiapkan untuk pengujian concurrency/locking MySQL, sementara pengujian fungsional unit cepat dapat menggunakan sqlite in-memory jika non-locking atau dialihkan ke MySQL sesuai kebutuhan PRD.

## 3. Revisi Desain & Aset Media (Permintaan Pengguna)
- **Penghapusan Banner Mode Simulasi**: Komponen `SimulationBanner` dan teks penanda "MODE SIMULASI" dihilangkan dari seluruh layout (Public, Penabung, Peternak, Guest) dan halaman tagihan atas instruksi pengguna agar tampilan bersih dan menyerupai aplikasi produksi.
- **Polesan Responsif & Spacing Katalog**: Kontainer utama halaman katalog (`Katalog/Index.jsx` dan `Katalog/Show.jsx`) ditambahkan padding horizontal responsif (`px-4 sm:px-6 lg:px-8`) agar kartu tidak mentok ke sisi kiri pada layar sempit/mobile, serta padding bawah (`pb-16 sm:pb-24`) agar kartu terbawah tidak menempel ke footer.
- **Aset Gambar Nyata Hewan Kurban**: Mengunduh foto berkualitas tinggi hewan kurban asli (domba, kambing, sapi) dari internet ke `public/animals/` dan menyediakan utilitas resolver `getAnimalImageUrl` dengan fallback gambar per jenis hewan kurban. Seeder `DatabaseSeeder` diperbarui mengarahkan media ke file gambar tersebut.

