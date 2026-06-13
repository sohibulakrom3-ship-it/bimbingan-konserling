# Sistem Informasi BK SMP Muhammadiyah Cileungsi

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Status](https://img.shields.io/badge/status-demo%20ready-2ea44f?style=flat-square)
![License](https://img.shields.io/badge/license-TBD-lightgrey?style=flat-square)

Aplikasi web Bimbingan Konseling berbasis PHP native untuk membantu sekolah mengelola layanan konsultasi siswa, jadwal konseling, catatan perkembangan, pelanggaran, komunikasi orang tua, dan administrasi data BK.

Project ini dapat dijalankan dengan MySQL atau langsung memakai data demo JSON, sehingga cocok untuk demo, pembelajaran, dan pengembangan awal sistem informasi sekolah.

## Daftar Isi

- [Fitur](#fitur)
- [Hak Akses](#hak-akses)
- [Teknologi](#teknologi)
- [Struktur Project](#struktur-project)
- [Persyaratan](#persyaratan)
- [Instalasi](#instalasi)
- [Konfigurasi Database](#konfigurasi-database)
- [Akun Demo](#akun-demo)
- [Validasi Cepat](#validasi-cepat)
- [Catatan Keamanan](#catatan-keamanan)
- [Roadmap](#roadmap)
- [Lisensi](#lisensi)

## Fitur

- Landing page profil layanan BK sekolah.
- Login dan dashboard sesuai role pengguna.
- Pengajuan konsultasi oleh siswa.
- Verifikasi status konsultasi oleh guru BK.
- Penjadwalan sesi konseling.
- Riwayat konseling siswa.
- Chat atau pesan antara siswa, orang tua, dan guru BK.
- Monitoring perkembangan anak untuk orang tua.
- Catatan perkembangan siswa oleh guru BK.
- Pencatatan pelanggaran dan poin kedisiplinan.
- Notifikasi internal untuk pengguna.
- Manajemen user dan data siswa oleh admin.
- Cetak laporan BK melalui fitur print browser.
- Backup data ke file JSON.
- Mode demo tanpa MySQL melalui `database/demo-data.json`.

## Hak Akses

| Role | Akses Utama |
| --- | --- |
| Siswa | Mengajukan konsultasi, melihat jadwal, membuka riwayat, membaca notifikasi, dan mengirim pesan ke guru BK. |
| Orang tua | Memantau data anak, melihat riwayat konseling, menerima notifikasi, dan berkomunikasi dengan guru BK. |
| Guru BK | Mengelola konsultasi, membuat jadwal, menulis catatan perkembangan, mencatat pelanggaran, dan mencetak laporan. |
| Admin | Mengelola akun pengguna, data siswa, statistik sistem, dan backup data. |

## Teknologi

- PHP native
- MySQL atau MariaDB
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
|   |-- demo-data.json
|   |-- mysql_bk_100_siswa_100_ortu_15_guru_1_admin.sql
|   `-- schema.sql
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

- PHP 8.x
- MySQL 8.x atau MariaDB, opsional untuk mode demo JSON
- Ekstensi PHP `pdo_mysql`, hanya dibutuhkan jika memakai database MySQL
- Web browser modern

## Instalasi

Clone repository:

```bash
git clone https://github.com/sohibulakrom3-ship-it/bimbingan-konserling.git
cd bimbingan-konserling
```

Jalankan server development PHP:

```bash
php -S localhost:8000 -t public
```

Buka aplikasi:

```text
http://localhost:8000
```

Jika MySQL belum aktif atau konfigurasi database belum sesuai, aplikasi tetap dapat berjalan memakai data demo dari `database/demo-data.json`.

## Konfigurasi Database

Konfigurasi koneksi database berada di `config/database.php`. Nilainya dapat diubah melalui environment variable berikut:

| Variable | Default | Keterangan |
| --- | --- | --- |
| `DB_HOST` | `127.0.0.1` | Host database |
| `DB_PORT` | `3306` | Port database |
| `DB_DATABASE` | `bk_muhammadiyah` | Nama database |
| `DB_USERNAME` | `root` | Username database |
| `DB_PASSWORD` | kosong | Password database |

Import skema dasar:

```bash
mysql -u root -p < database/schema.sql
```

Import data demo lengkap:

```bash
mysql -u root -p < database/mysql_bk_100_siswa_100_ortu_15_guru_1_admin.sql
```

Contoh konfigurasi di Windows PowerShell:

```powershell
$env:DB_HOST="127.0.0.1"
$env:DB_PORT="3306"
$env:DB_DATABASE="bk_muhammadiyah"
$env:DB_USERNAME="root"
$env:DB_PASSWORD="secret"
php -S localhost:8000 -t public
```

Contoh konfigurasi di Linux atau macOS:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_DATABASE=bk_muhammadiyah
export DB_USERNAME=root
export DB_PASSWORD=secret
php -S localhost:8000 -t public
```

## Akun Demo

Semua akun demo menggunakan password:

```text
password
```

Data minimal dari `database/schema.sql`:

| Role | Email |
| --- | --- |
| Siswa | `siswa@bk.test` |
| Orang tua | `ortu@bk.test` |
| Guru BK | `guru@bk.test` |
| Admin | `admin@bk.test` |

Data lengkap dari `database/mysql_bk_100_siswa_100_ortu_15_guru_1_admin.sql`:

| Role | Email |
| --- | --- |
| Siswa | `siswa001@bk.test` sampai `siswa100@bk.test` |
| Orang tua | `ortu001@bk.test` sampai `ortu100@bk.test` |
| Guru BK | `guru01@bk.test` sampai `guru15@bk.test` |
| Admin | `admin@bk.test` |

## Validasi Cepat

Jalankan pengecekan syntax PHP sebelum push perubahan:

```bash
php -l app/Auth.php
php -l app/Database.php
php -l app/DataStore.php
php -l public/action.php
php -l public/dashboard.php
php -l public/index.php
php -l public/login.php
```

Alur uji manual yang disarankan:

1. Buka halaman utama.
2. Login sebagai siswa dan ajukan konsultasi.
3. Login sebagai guru BK dan ubah status konsultasi.
4. Login sebagai orang tua dan cek monitoring anak.
5. Login sebagai admin dan cek daftar user serta data siswa.

## Catatan Keamanan

Beberapa praktik keamanan yang sudah diterapkan:

- Password disimpan menggunakan `password_hash`.
- Login diverifikasi menggunakan `password_verify`.
- Query database memakai PDO prepared statement.
- Form POST dilindungi CSRF token.
- Output HTML memakai escaping helper.
- Aksi dashboard dibatasi berdasarkan role.

Untuk penggunaan produksi, disarankan menambahkan HTTPS, konfigurasi session yang lebih ketat, validasi server-side yang lebih lengkap, audit permission per modul, dan backup database terjadwal.

## Roadmap

- [ ] Menambahkan file `LICENSE`.
- [ ] Menambahkan panduan deployment ke hosting.
- [ ] Menambahkan pagination dan pencarian data.
- [ ] Menambahkan export laporan dalam format PDF atau Excel.
- [ ] Menambahkan automated test untuk auth, helper, dan datastore.
- [ ] Menambahkan migrasi database yang lebih rapi.
- [ ] Menambahkan halaman profil pengguna.
- [ ] Menambahkan pengaturan sekolah dan tahun ajaran.

## Kontribusi

Kontribusi terbuka untuk perbaikan bug, dokumentasi, peningkatan UI, validasi form, dan pengembangan modul baru.

Sebelum membuat pull request:

- Gunakan branch terpisah untuk setiap fitur atau perbaikan.
- Jelaskan perubahan dengan singkat dan jelas.
- Pastikan halaman utama, login, dan dashboard tetap berjalan.
- Sertakan langkah uji manual pada deskripsi pull request.

Contoh format commit:

```text
feat: tambah filter status konsultasi
fix: validasi email saat tambah user
docs: perbarui instruksi instalasi
```

## Lisensi

Lisensi project belum ditentukan karena file `LICENSE` belum tersedia.

Jika project akan dibuka untuk publik, tambahkan lisensi open source seperti MIT, Apache-2.0, atau GPL-3.0 sesuai kebutuhan.

## Maintainer

Project ini dikembangkan untuk kebutuhan Sistem Informasi Bimbingan Konseling SMP Muhammadiyah Cileungsi.
