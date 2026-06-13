# Sistem Informasi BK SMP Muhammadiyah Cileungsi

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Status](https://img.shields.io/badge/status-demo%20ready-2ea44f?style=flat-square)
![License](https://img.shields.io/badge/license-TBD-lightgrey?style=flat-square)

Aplikasi web Bimbingan Konseling berbasis PHP dan MySQL untuk membantu sekolah mengelola konsultasi siswa, jadwal BK, catatan perkembangan, monitoring orang tua, dan administrasi layanan konseling.

Project ini dibuat sederhana agar mudah dipelajari, dijalankan secara lokal, dan dikembangkan kembali. Jika MySQL belum tersedia, aplikasi tetap bisa dicoba menggunakan data demo JSON.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Struktur Project](#struktur-project)
- [Persyaratan](#persyaratan)
- [Quick Start](#quick-start)
- [Konfigurasi Database](#konfigurasi-database)
- [Akun Demo](#akun-demo)
- [Alur Pengembangan](#alur-pengembangan)
- [Kontribusi](#kontribusi)
- [Keamanan](#keamanan)
- [Roadmap](#roadmap)
- [Lisensi](#lisensi)

## Fitur Utama

- Landing page informatif untuk layanan BK sekolah.
- Login dan dashboard berdasarkan role pengguna.
- Role siswa, orang tua, guru BK, dan admin.
- Pengajuan konsultasi siswa.
- Chat/pesan antara siswa, orang tua, dan guru BK.
- Verifikasi status konsultasi dan penjadwalan sesi BK.
- Catatan perkembangan siswa.
- Pencatatan pelanggaran dan poin kedisiplinan.
- Notifikasi internal untuk pengguna.
- Manajemen user dan data siswa oleh admin.
- Backup data demo dalam format JSON.
- Fallback data lokal melalui `database/demo-data.json` jika MySQL belum aktif.

## Tech Stack

- PHP native
- MySQL
- PDO prepared statement
- HTML, CSS, dan JavaScript tanpa framework frontend
- JSON datastore untuk mode demo lokal

## Struktur Project

```text
.
|-- app/
|   |-- Auth.php
|   |-- Database.php
|   |-- DataStore.php
|   |-- helpers.php
|   `-- icons.php
|-- config/
|   `-- database.php
|-- database/
|   |-- schema.sql
|   |-- mysql_bk_100_siswa_100_ortu_15_guru_1_admin.sql
|   `-- demo-data.json
|-- public/
|   |-- assets/
|   |-- action.php
|   |-- dashboard.php
|   |-- index.php
|   |-- login.php
|   |-- logout.php
|   `-- submit-consultation.php
`-- README.md
```

## Persyaratan

- PHP 8.x atau versi yang kompatibel dengan typed property dan syntax modern PHP.
- MySQL 8.x atau MariaDB yang kompatibel.
- Ekstensi PHP `pdo_mysql` jika ingin memakai database MySQL.

Untuk mencoba tampilan dan flow dasar, MySQL tidak wajib karena aplikasi dapat memakai `database/demo-data.json`.

## Quick Start

Clone repository:

```bash
git clone <repository-url>
cd <nama-folder-project>
```

Jalankan server lokal PHP:

```bash
php -S localhost:8000 -t public
```

Buka aplikasi di browser:

```text
http://localhost:8000
```

Jika MySQL belum dikonfigurasi, aplikasi akan memakai data demo lokal dari `database/demo-data.json`.

## Konfigurasi Database

Konfigurasi koneksi ada di `config/database.php` dan dapat diatur melalui environment variable berikut:

| Variable | Default | Keterangan |
| --- | --- | --- |
| `DB_HOST` | `127.0.0.1` | Host database |
| `DB_PORT` | `3306` | Port database |
| `DB_DATABASE` | `bk_muhammadiyah` | Nama database |
| `DB_USERNAME` | `root` | Username database |
| `DB_PASSWORD` | kosong | Password database |

### Import Data Minimal

```bash
mysql -u root -p < database/schema.sql
```

### Import Data Lengkap

File berikut berisi data lebih lengkap untuk kebutuhan demo:

```bash
mysql -u root -p < database/mysql_bk_100_siswa_100_ortu_15_guru_1_admin.sql
```

### Contoh Environment Variable

Linux/macOS:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_DATABASE=bk_muhammadiyah
export DB_USERNAME=root
export DB_PASSWORD=secret
php -S localhost:8000 -t public
```

Windows PowerShell:

```powershell
$env:DB_HOST="127.0.0.1"
$env:DB_PORT="3306"
$env:DB_DATABASE="bk_muhammadiyah"
$env:DB_USERNAME="root"
$env:DB_PASSWORD="secret"
php -S localhost:8000 -t public
```

## Akun Demo

Semua akun demo memakai password:

```text
password
```

Jika memakai `database/schema.sql`:

| Role | Email |
| --- | --- |
| Siswa | `siswa@bk.test` |
| Orang tua | `ortu@bk.test` |
| Guru BK | `guru@bk.test` |
| Admin | `admin@bk.test` |

Jika memakai `database/mysql_bk_100_siswa_100_ortu_15_guru_1_admin.sql`:

| Role | Email |
| --- | --- |
| Siswa | `siswa001@bk.test` sampai `siswa100@bk.test` |
| Orang tua | `ortu001@bk.test` sampai `ortu100@bk.test` |
| Guru BK | `guru01@bk.test` sampai `guru15@bk.test` |
| Admin | `admin@bk.test` |

## Alur Pengembangan

1. Buat branch baru dari branch utama.
2. Jalankan aplikasi secara lokal.
3. Uji perubahan melalui role yang terdampak.
4. Pastikan tidak ada error PHP saat membuka halaman utama, login, dan dashboard.
5. Kirim pull request dengan deskripsi perubahan yang jelas.

Contoh pemeriksaan cepat:

```bash
php -l app/Auth.php
php -l app/Database.php
php -l app/DataStore.php
php -l public/action.php
php -l public/dashboard.php
```

## Kontribusi

Kontribusi sangat terbuka untuk perbaikan bug, peningkatan UI, validasi form, dokumentasi, dan pengembangan modul baru.

Sebelum mengirim pull request:

- Jelaskan masalah atau fitur yang diselesaikan.
- Buat perubahan sekecil mungkin sesuai kebutuhan.
- Hindari mengubah file data demo jika tidak diperlukan.
- Sertakan langkah uji manual pada deskripsi pull request.
- Pastikan perubahan tidak merusak flow login dan dashboard multi-role.

Contoh judul pull request:

```text
feat: tambah filter status konsultasi guru BK
fix: validasi email saat admin membuat user
docs: perbarui instruksi setup database
```

## Keamanan

Beberapa praktik keamanan yang sudah diterapkan:

- Password disimpan menggunakan `password_hash`.
- Login diverifikasi menggunakan `password_verify`.
- Query database memakai PDO prepared statement.
- Form POST dilindungi CSRF token.
- Output HTML menggunakan escaping helper.
- Akses aksi dashboard dibatasi berdasarkan role.

Jika menemukan celah keamanan, jangan membuat issue publik berisi detail eksploit. Hubungi maintainer project terlebih dahulu atau buat laporan privat jika platform repository mendukungnya.

## Roadmap

- [ ] Menambahkan file `LICENSE`.
- [ ] Menambahkan panduan deployment.
- [ ] Menambahkan automated test untuk helper, auth, dan datastore.
- [ ] Menambahkan migrasi database yang lebih terstruktur.
- [ ] Menambahkan pagination dan pencarian untuk data besar.
- [ ] Menambahkan export laporan yang lebih lengkap.
- [ ] Menambahkan dokumentasi API internal jika modul dipisah.

## Lisensi

Lisensi project belum ditentukan karena file `LICENSE` belum tersedia di repository.

Jika project ini akan dibuka untuk publik, disarankan menambahkan lisensi open source seperti MIT, Apache-2.0, atau GPL-3.0 sesuai kebutuhan distribusi dan kontribusi.

## Maintainer

Project ini dikembangkan untuk kebutuhan Sistem Informasi Bimbingan Konseling SMP Muhammadiyah Cileungsi.

Issue, diskusi, dan pull request dapat digunakan untuk melacak pengembangan berikutnya setelah repository dipublikasikan.
