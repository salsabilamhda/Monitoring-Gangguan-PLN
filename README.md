# Monitoring Gangguan PLN - UP3 Ponorogo

Aplikasi dashboard web interaktif untuk memonitor, menganalisis, dan merekap data gangguan listrik (harian dan bulanan) di wilayah kerja PLN UP3 Ponorogo.

## Fitur Utama

- **Rekap Harian & Bulanan:** Laporan komprehensif data gangguan listrik.
- **Filter Interaktif:** Memfilter data berdasarkan tahun, kategori gangguan (PMT, REC/PMCB, ALL), serta unit ULP terkait.
- **Ekspor Data:** Fitur ekspor laporan langsung ke format Excel, PDF, atau cetak fisik (Print) menggunakan DataTables Buttons.
- **Desain Responsif:** Antarmuka modern yang nyaman diakses di berbagai ukuran layar.
- **Shim Kompatibilitas PHP 7/8:** Mendukung deployment pada server modern (PHP 7.x atau PHP 8.x) meskipun codebase warisan menggunakan ekstensi `mysql_*` lama, berkat adanya modul pembantu `mysql_shim.php`.

## Struktur Direktori Penting

- `/assets` - File aset pendukung (CSS, JS, gambar, template dashboard Zoter).
- `/absen` - Modul tambahan sistem kehadiran.
- `connect.php` - Konfigurasi koneksi database utama.
- `mysql_shim.php` - Skrip pembantu kompatibilitas PHP versi 7.x dan 8.x.
- `index.php` - Halaman utama (dashboard frame wrapper).
- `rekapharian.php` & `monitharian.php` - Halaman entri filter harian dan tabel data harian.
- `rekapbulanan.php` & `monitbulanan.php` - Halaman entri filter bulanan dan tabel data bulanan.
- `jart2779_jaringan.sql` - Salinan cadangan (*backup*) struktur & data database MySQL.

## Langkah Instalasi & Penggunaan Lokal

### Prasyarat
- Aplikasi Local Server seperti **Laragon** (sangat disarankan) atau **XAMPP**.
- PHP minimal versi 5.6 hingga versi 8.x terbaru.
- MySQL / MariaDB.

### Langkah-langkah
1. **Clone/Salin Proyek:**
   Tempatkan folder proyek ini ke dalam direktori root server lokal Anda (misal `C:\laragon\www\Monitoring-PLN` atau `C:\xampp\htdocs\Monitoring-PLN`).

2. **Siapkan Database:**
   - Buka phpMyAdmin atau aplikasi manajemen database pilihan Anda (seperti HeidiSQL).
   - Buat database baru bernama **`jart2779_jaringan`**.
   - Impor berkas database **`jart2779_jaringan.sql`** yang berada di root folder proyek ini ke dalam database baru tersebut.

3. **Konfigurasi Koneksi:**
   Buka file [connect.php](file:///c:/laragon/www/Monitoring-PLN/connect.php) dan sesuaikan kredensial koneksi database lokal Anda jika diperlukan:
   ```php
   $host  = "localhost";
   $user  = "root";
   $pass  = "";
   $dbase = "jart2779_jaringan";
   ```

4. **Jalankan Aplikasi:**
   - Aktifkan Apache & MySQL pada control panel Laragon/XAMPP.
   - Buka browser Anda dan akses tautan: `http://localhost/Monitoring-PLN`

## Catatan Deployment Server (cPanel)

Aplikasi ini aman dideploy pada hosting cPanel modern dengan PHP versi 7.x atau 8.x karena telah terpasang **`mysql_shim.php`** yang menjembatani fungsi `mysql_*` dengan engine modern `mysqli`. 
Pastikan file `mysql_shim.php` selalu ikut terunggah, serta ubah informasi kredensial database di file `connect.php` sesuai dengan konfigurasi database di cPanel Anda.
