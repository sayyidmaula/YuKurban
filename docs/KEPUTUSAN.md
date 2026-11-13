# Catatan Keputusan Teknis & Penyesuaian

Dokumen ini mencatat keputusan arsitektural dan penyesuaian terhadap PRD, framework versi terkini (Laravel 13, Vite 8), serta lingkungan eksekusi.

## 1. Lingkungan Eksekusi & Sandbox (Windows)
- Pada lingkungan Windows sandbox, fungsi bawaan PHP `is_readable()` mengembalikan `false` akibat penolakan evaluasi ACL oleh restricted token Windows API, sedangkan `is_file()` dan `fopen()` / `file_get_contents()` dapat membaca file dengan baik.
- `vendor/phpunit/phpunit/src/TextUI/Configuration/BootstrapLoader.php` disesuaikan menggunakan `is_file($filename)` agar eksekusi test runner PHPUnit 12 dapat memuat bootstrap autoload composer.

## 2. Pengaturan Timezone & Database Test
- Timezone aplikasi diatur ke `Asia/Jakarta` di `config/app.php`.
- Database testing `yukurban_test` disiapkan untuk pengujian concurrency/locking MySQL, sementara pengujian fungsional unit cepat dapat menggunakan sqlite in-memory jika non-locking atau dialihkan ke MySQL sesuai kebutuhan PRD.

