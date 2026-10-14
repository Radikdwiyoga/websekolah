# Website Sekolah

Sistem informasi sekolah berbasis Laravel yang menggabungkan **website profil sekolah** (halaman publik yang kontennya bisa dikelola sendiri dari panel admin) dengan **platform akademik** untuk absensi digital, tugas & penilaian, izin & sakit, dan pengumuman.

[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777bb4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-ff2d20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06b6d4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![Vite](https://img.shields.io/badge/Vite-8-646cff?logo=vite&logoColor=white)](https://vitejs.dev/)

---

## Daftar Isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Persyaratan](#persyaratan)
- [Instalasi](#instalasi)
- [Akun Demo](#akun-demo)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Pengujian](#testing)
- [Struktur Direktori](#struktur-direktori)
- [Catatan Deployment](#catatan-deployment)
- [Lisensi](#lisensi)

---

## Fitur

Aplikasi memiliki panel berbeda untuk setiap peran (**Admin**, **Guru**, **Siswa**, dan **Orang Tua**) dengan navigasi sidebar yang menyesuaikan hak akses.

### 🌐 Website Publik

- **Halaman utama** — hero, keunggulan sekolah, dan program/layanan yang seluruh teks dan ikonnya dapat diubah dari admin.
- **Profil sekolah** — identitas, alamat, kontak, peta, dan media sosial.
- **Agenda & Galeri** — kegiatan sekolah dengan media foto (JPG/PNG/WebP) atau video (unggah MP4/WebM/MOV atau embed YouTube/Vimeo).
- **Pengumuman** dan **mode pemeliharaan** (maintenance) yang bisa diaktifkan dari admin.

### Admin Sekolah

- **Data master**: siswa, guru, orang tua/wali, kelas, mata pelajaran, dan tahun ajaran (dengan penugasan guru–mapel–kelas).
- **Akademik**: absensi, rekap absensi, tugas & penilaian, serta pengajuan izin & sakit.
- **Informasi**: kelola pengumuman untuk seluruh warga sekolah.
- **Administrasi**:
  - **Profil Sekolah** – identitas, logo, kontak, dan tautan sosial media.
  - **Halaman Utama** – hero, daftar keunggulan, dan program (dengan pemilih ikon visual).
  - **Agenda & Galeri** – CRUD agenda dengan unggah foto/video.
  - **Pengaturan Sistem** – waktu absensi, bobot nilai, kata sandi default, template pesan WhatsApp, dsb.
  - **Backup & Ekspor** – rekap absensi dan tugas/nilai dalam format **Excel (multi-sheet)** dan **PDF**.
  - **Maintenance** & **System Log**.
- Dua tingkat admin: **Super Admin** dan **Admin Operasional**.

### Guru

- **Kelas Saya** dan penugasan mengajar.
- **Input Absensi** manual maupun **scan QR code** siswa, termasuk buka/tutup sesi absensi.
- **Rekap Absensi**, **Tugas & Penilaian**, serta verifikasi **Izin & Sakit**.

### Siswa

- **Kehadiran** pribadi (termasuk kartu QR permanen untuk absensi).
- **Tugas**, **Nilai**, **Izin & Sakit**, dan **Pengumuman**.

### Orang Tua

- **Kehadiran anak**, **Tugas anak**, dan **Nilai anak**.
- Pengajuan **Izin & Sakit** serta menerima **pengumuman** sekolah.

---

## Teknologi

| Lapisan | Teknologi |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13 |
| Frontend | Blade, Tailwind CSS 4, Vite 8, JavaScript (vanilla) |
| Database | SQLite (default) — kompatibel dengan MySQL/MariaDB & PostgreSQL |
| Ekspor | `maatwebsite/excel` (Excel), `barryvdh/laravel-dompdf` (PDF) |
| QR Code | `bacon/bacon-qr-code` (generate) + `jsqr` (scan via kamera) |
| Testing | PHPUnit 12 |
| Kode style | Laravel Pint |

---

## Persyaratan

- PHP **8.3** atau lebih baru dengan ekstensi `gd`, `mbstring`, `openssl`, `pdo`, `zip`
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) 20+ dan npm

---

## Instalasi

1. **Clone repositori**

   ```bash
   git clone https://github.com/Radikdwiyoga/websekolah.git
   cd websekolah
   ```

2. **Instal dependensi, siapkan environment, migrasi, dan build aset**

   ```bash
   composer run setup
   ```

   Perintah tersebut menjalankan: `composer install`, menyalin `.env.example` menjadi `.env`, `key:generate`, `migrate`, `npm install`, dan `npm run build`.

3. **Isi data contoh (opsional, disarankan untuk pengembangan)**

   ```bash
   php artisan db:seed
   ```

4. **Buat symbolic link penyimpanan** (untuk logo sekolah, foto/video agenda, lampiran izin)

   ```bash
   php artisan storage:link
   ```

> Secara default aplikasi memakai **SQLite**. Untuk memakai MySQL, ubah `DB_CONNECTION` dan variabel `DB_*` pada file `.env`, lalu jalankan ulang `php artisan migrate`.

---

## Akun Demo

Setelah menjalankan `php artisan db:seed`, tersedia akun berikut (semua dengan kata sandi **`password`**):

| Peran | Username | Email |
| --- | --- | --- |
| Super Admin | `superadmin` | superadmin@sekolah.test |
| Admin Operasional | `admin` | admin@sekolah.test |
| Guru | `guru` | guru@sekolah.test |
| Guru | `guru.andi` | guru.andi@sekolah.test |
| Guru | `guru.rina` | guru.rina@sekolah.test |
| Guru | `guru.joko` | guru.joko@sekolah.test |
| Siswa | `siswa` | siswa@sekolah.test |
| Orang Tua | `ortu` | ortu@sekolah.test |

Login memakai kolom **username** (bisa juga NIS untuk siswa) beserta pemilihan peran.

---

## Menjalankan Aplikasi

Jalankan server pengembangan (server PHP + Vite + queue + log sekaligus):

```bash
composer run dev
```

Atau jalankan secara terpisah:

```bash
php artisan serve      # http://localhost:8000
npm run dev            # Vite dev server (hot reload)
```

### Testing

```bash
composer run test
# atau
php artisan test --compact
```

Test dijalankan pada database SQLite in-memory (lihat `phpunit.xml`), sehingga tidak mengganggu data pengembangan.

### Format kode

```bash
vendor/bin/pint
```

---

## Struktur Direktori

```
app/
├── Enums/          # Enum domain (peran, status absensi, dll.)
├── Exports/        # Definisi ekspor Excel & PDF
├── Http/
│   ├── Controllers/
│   └── Requests/   # Validasi form per domain
├── Models/
└── Services/       # Logika bisnis (absensi, penilaian, backup, konten, dsb.)

resources/
├── js/             # Skrip UI (vanilla JS)
├── css/            # Tailwind CSS
└── views/
    ├── admin/      # Panel admin
    ├── attendance/ # Modul absensi
    ├── components/ # Komponen Blade yang dapat dipakai ulang
    └── exports/    # Template PDF

tests/Feature/      # Test fitur per modul
```

---

## Catatan Deployment

- Wajib menjalankan `php artisan storage:link` agar media (logo sekolah, foto/video agenda, lampiran izin) dapat diakses publik.
- Jalankan `npm run build` setiap kali ada perubahan aset frontend.
- Gunakan `php artisan migrate --force` (bukan `migrate:fresh`) saat deploy ke produksi.
- Aplikasi siap dideploy ke [Laravel Cloud](https://cloud.laravel.com/).

---

## Lisensi

Proyek ini bersifat open-source dengan lisensi [MIT](https://opensource.org/licenses/MIT).
