# DESIGN.md — Website Sekolah

> Dokumen desain UI/UX untuk Website Sekolah berdasarkan PRD `PRD Website Sekolah.md`.
> Versi: 1.0 · Tanggal: 9 Oktober 2026

---

## 1. Tujuan Desain

Website sekolah dirancang sebagai satu platform untuk:

- Absensi digital siswa.
- Notifikasi WhatsApp kepada orang tua setelah perubahan status kehadiran.
- Pengelolaan tugas dan penilaian.
- Rekap absensi dan nilai.
- Pengumuman dan informasi sekolah.
- Administrasi akun, pengaturan, maintenance, backup, dan system log.

Desain harus mengutamakan **sederhana, cepat dipahami, responsive, aman, dan mudah digunakan melalui HP**.

PRD menetapkan empat role utama: Siswa, Guru, Orang Tua, dan Admin Sekolah.

---

# 2. Prinsip UI/UX

## 2.1 Mobile First

Prioritas tampilan:

1. Mobile
2. Tablet
3. Desktop

Target utama:

- Siswa: HP.
- Orang tua: HP.
- Guru: HP/tablet/desktop.
- Admin: desktop/tablet.

## 2.2 Role-Based Experience

Setiap pengguna hanya melihat menu yang relevan dengan role-nya.

### Siswa

Fokus:
- Absensi
- Tugas
- Nilai
- Pengumuman

### Guru

Fokus:
- Kelas
- Absensi
- Tugas
- Penilaian
- Rekap

### Orang Tua

Fokus:
- Kehadiran anak
- Tugas dan nilai
- Pengumuman

### Admin

Fokus:
- Data sekolah
- Rekap
- Akun
- Pengumuman
- Pengaturan
- Maintenance
- System log

---

# 3. Design Direction

## 3.1 Visual Style

Gunakan gaya:

- Modern
- Clean
- Friendly
- Professional
- Education-oriented
- Tidak terlalu ramai
- Banyak whitespace
- Card-based interface
- Rounded corner
- Soft shadow
- Icon sederhana
- Typography mudah dibaca

## 3.2 Karakter Visual

Website harus terasa:

> "Modern school management platform yang mudah digunakan siswa, guru, dan orang tua."

Hindari:

- Gradient berlebihan.
- Terlalu banyak warna.
- Dashboard penuh angka tanpa hierarki.
- Tabel yang terlalu padat pada mobile.
- Animasi berlebihan.

---

# 4. Color System

## Primary

```text
Primary 900  #0B2A4A
Primary 800  #123E6B
Primary 700  #155FA0
Primary 600  #1976D2
Primary 500  #2196F3
Primary 100  #E3F2FD
Primary 50   #F5FAFF
```

Warna primary digunakan untuk:

- Button utama
- Link
- Active navigation
- Icon utama
- Highlight
- CTA

## Semantic Colors

### Success

```text
Success 700 #15803D
Success 500 #22C55E
Success 100 #DCFCE7
```

Digunakan untuk:

- Hadir
- Berhasil
- Terkirim
- Selesai

### Warning

```text
Warning 700 #B45309
Warning 500 #F59E0B
Warning 100 #FEF3C7
```

Digunakan untuk:

- Terlambat
- Menunggu
- Deadline mendekat

### Error

```text
Error 700 #B91C1C
Error 500 #EF4444
Error 100 #FEE2E2
```

Digunakan untuk:

- Gagal
- Tidak hadir
- Error sistem
- Deadline terlewat

### Info

```text
Info 700 #0369A1
Info 500 #0EA5E9
Info 100 #E0F2FE
```

## Neutral

```text
Neutral 950 #111827
Neutral 900 #1F2937
Neutral 700 #374151
Neutral 600 #4B5563
Neutral 500 #6B7280
Neutral 400 #9CA3AF
Neutral 300 #D1D5DB
Neutral 200 #E5E7EB
Neutral 100 #F3F4F6
Neutral 50  #F9FAFB
White       #FFFFFF
```

---

# 5. Typography

Gunakan font modern sans-serif.

Rekomendasi:

```text
Font Family:
Inter, system-ui, sans-serif
```

## Type Scale

| Style | Size | Weight |
|---|---:|---:|
| Display | 48px | 700 |
| H1 | 36px | 700 |
| H2 | 30px | 700 |
| H3 | 24px | 600 |
| H4 | 20px | 600 |
| Body Large | 18px | 400 |
| Body | 16px | 400 |
| Body Small | 14px | 400 |
| Caption | 12px | 400 |
| Button | 14px | 600 |

Mobile:

```text
H1: 28px
H2: 24px
H3: 20px
Body: 14–16px
```

---

# 6. Spacing System

Gunakan kelipatan 4px.

```text
4
8
12
16
20
24
32
40
48
64
80
96
```

Rekomendasi:

- Card padding: 20–24px
- Section spacing: 48–80px
- Form spacing: 16px
- Button height: 44–48px
- Input height: 44–48px

---

# 7. Border Radius

```text
Small   6px
Medium  10px
Large   14px
XL      20px
Pill    999px
```

Rekomendasi:

- Input: 10px
- Button: 10px
- Card: 14px
- Hero: 20px
- Badge: 999px

---

# 8. Shadow

Gunakan shadow ringan.

```text
Shadow SM:
0 1px 3px rgba(0,0,0,0.08)

Shadow MD:
0 4px 12px rgba(0,0,0,0.08)

Shadow LG:
0 12px 30px rgba(0,0,0,0.10)
```

---

# 9. Grid System

## Desktop

```text
Max Width: 1280px
Columns: 12
Gutter: 24px
```

## Tablet

```text
Columns: 8
Gutter: 20px
```

## Mobile

```text
Columns: 4
Gutter: 16px
```

---

# 10. Global Navigation

## Website Publik

Navbar:

```text
Logo | Beranda | Tentang | Program | Berita | Kontak | [Masuk]
```

Mobile:

```text
Logo                         ☰
```

Menu mobile menggunakan drawer.

## Dashboard

Desktop:

```text
┌──────────────┬─────────────────────────────┐
│              │ Header                      │
│   Sidebar    ├─────────────────────────────┤
│              │ Content                     │
│              │                             │
└──────────────┴─────────────────────────────┘
```

Sidebar berisi:

- Dashboard
- Absensi
- Tugas
- Nilai / Penilaian
- Pengumuman
- Data Master
- Rekap
- Pengaturan
- Maintenance

Menu disesuaikan berdasarkan role.

Mobile menggunakan:

- Top bar
- Hamburger menu
- Bottom navigation untuk menu paling penting bila diperlukan

---

# 11. Homepage

## Tujuan

Memberikan informasi sekolah sekaligus menjadi entry point menuju sistem.

## Struktur

```text
Navbar
↓
Hero Section
↓
Keunggulan Sekolah
↓
Program Sekolah
↓
Informasi / Pengumuman
↓
Statistik Sekolah
↓
CTA Login
↓
Footer
```

## Hero

Headline:

> Bersama Membangun Generasi Masa Depan

Subheadline:

> Sekolah yang aman, nyaman, dan mendukung perkembangan potensi setiap siswa.

CTA:

- Lihat Profil Sekolah
- Hubungi Kami

Visual:

- Foto siswa
- Ilustrasi sekolah
- Background terang

## Feature Cards

Minimal 4:

1. Pendidikan Berkualitas
2. Lingkungan Aman
3. Pengembangan Karakter
4. Dukungan Orang Tua

---

# 12. Halaman Login

Layout desktop:

```text
┌──────────────────────┬─────────────────────────┐
│                      │                         │
│  Branding Sekolah    │       Form Login       │
│  Illustration        │                         │
│                      │  Role selector          │
│                      │  Username / NIS         │
│                      │  Password               │
│                      │  Lupa password          │
│                      │  [Masuk]                │
└──────────────────────┴─────────────────────────┘
```

Role selector:

```text
[Siswa] [Guru] [Orang Tua] [Admin]
```

Input:

- NIS / Username
- Password

State:

- Default
- Focus
- Error
- Disabled
- Loading

---

# 13. Dashboard Siswa

## Header

```text
Selamat pagi, Budi
Tetap semangat belajar hari ini!
```

## KPI Cards

```text
┌────────────┐ ┌────────────┐ ┌────────────┐
│ Hadir      │ │ Tugas      │ │ Nilai      │
│ 06.52      │ │ 3 Aktif     │ │ 85         │
└────────────┘ └────────────┘ └────────────┘
```

## Konten

### Absensi Hari Ini

Status besar:

```text
✓ HADIR
06.52
```

### Tugas Terbaru

Setiap card menampilkan:

- Mata pelajaran
- Judul
- Deadline
- Status

Status:

- Belum dikerjakan
- Dalam pengerjaan
- Selesai
- Terlambat

---

# 14. Halaman Absensi Siswa

## State Belum Absen

```text
Absensi Hari Ini

Kelas 7A
07.00 – 07.30

[ Absen Sekarang ]

atau

[ Scan QR Code ]
```

## State Sudah Absen

```text
✓ Anda sudah hadir

Waktu:
06.52

Status:
Hadir
```

## State Terlambat

Gunakan warna warning.

```text
Anda tercatat terlambat

Waktu:
07.41
```

QR Code harus memiliki countdown karena PRD menetapkan QR berubah setiap 30 detik.

---

# 15. Dashboard Guru

## KPI

```text
4 Kelas Diampu
32 Total Siswa
3 Tugas Aktif
28 Tugas Perlu Dinilai
```

## Jadwal Hari Ini

Tampilkan:

- Jam
- Kelas
- Mata pelajaran
- Ruangan

## Tugas Perlu Dinilai

Card menampilkan:

- Judul
- Kelas
- Jumlah pengumpulan
- Jumlah belum dinilai
- CTA "Nilai"

---

# 16. Halaman Absensi Guru

Header:

```text
Absensi > Kelas 7A
```

Session card:

```text
Sesi Absensi Sedang Berjalan

07.00 – 07.30

Waktu tersisa:
12 menit 30 detik

[ Tutup Sesi ]
```

QR:

```text
┌─────────────┐
│             │
│   QR CODE   │
│             │
└─────────────┘

Berlaku 30 detik
[Refresh QR]
```

## Attendance Table

Kolom:

| No | Nama | Status | Waktu | Aksi |
|---|---|---|---|---|

Status:

- Hadir
- Terlambat
- Belum Absen
- Izin
- Sakit
- Alfa

Aksi:

- Ubah status
- Lihat catatan

---

# 17. Membuat Tugas

Form:

```text
Judul Tugas
[_____________________]

Mata Pelajaran
[_____________________]

Kelas
[_____________________]

Jenis Soal
○ Pilihan Ganda
○ Isian Singkat
○ Esai
○ Upload File

Deskripsi
[_____________________]

Deadline
[_____________________]

Bobot Nilai
[_____________________]

[ Simpan Draft ] [ Publikasikan ]
```

Jenis tugas mengikuti PRD:

- Pilihan ganda → otomatis.
- Isian singkat → otomatis.
- Esai → manual guru.
- Upload file → manual guru.

---

# 18. Halaman Pengerjaan Tugas Siswa

Header:

```text
Matematika
Bab 2 — Persamaan Linear
Deadline: 12 Oktober 2026
```

Progress:

```text
Soal 4 dari 10
████████░░ 40%
```

Navigasi:

```text
[1] [2] [3] [4] [5] ...
```

Button:

```text
[ Sebelumnya ]       [ Berikutnya ]
```

Submit:

```text
[ Kirim Jawaban ]
```

Konfirmasi:

> Apakah Anda yakin ingin mengirim jawaban? Setelah dikirim, jawaban dapat diubah selama masih dalam batas waktu.

---

# 19. Dashboard Orang Tua

Header:

```text
Halo, Ibu Melati
Berikut informasi perkembangan belajar anak.
```

Child selector:

```text
Anak:
[Budi Santoso — 7A ▼]
```

## KPI

```text
Hadir       20 hari
Terlambat   2 hari
Tidak Hadir 1 hari
Nilai       85
```

## Riwayat Absensi

| Tanggal | Status | Waktu |
|---|---|---|
| 9 Okt | Hadir | 06.52 |
| 8 Okt | Terlambat | 07.41 |
| 7 Okt | Hadir | 06.49 |
| 6 Okt | Izin | - |

## Tugas Anak

Tampilkan status:

- Belum dikerjakan
- Dalam pengerjaan
- Selesai
- Terlambat

---

# 20. Notifikasi WhatsApp

UI tidak menggantikan WhatsApp, tetapi dashboard harus memperlihatkan status pengiriman.

Contoh:

```text
Notifikasi Kehadiran

✓ Terkirim
Ananda Budi telah hadir di sekolah
09 Okt 2026 · 06.52
```

Untuk gagal:

```text
✕ Gagal dikirim

Nomor:
08xxxxxxxxxx

Percobaan:
3/3

[ Coba Lagi ]
```

Admin dapat melihat log pengiriman.

---

# 21. Rekap Absensi

Filter:

```text
Periode       [ Oktober 2026 ]
Kelas         [ Semua Kelas ]
Status        [ Semua Status ]

[ Tampilkan ]
```

Summary:

```text
Total Siswa     32
Hadir           28
Terlambat        2
Izin             1
Sakit            1
Alfa             0
```

Table:

| No | Nama | Kelas | Hadir | Terlambat | Izin | Sakit | Alfa | Persentase |
|---|---|---|---:|---:|---:|---:|---:|---:|

CTA:

```text
[ Unduh Excel ] [ Unduh PDF ]
```

---

# 22. Pengumuman

## List

Card/table:

```text
Libur Semester Ganjil
Target: Semua
Tanggal: 1 Okt 2026
Status: Dipublikasikan
```

Status:

- Draft
- Dijadwalkan
- Dipublikasikan
- Arsip

## Create Announcement

Field:

- Judul
- Isi
- Target
- Tanggal publikasi
- Lampiran
- Tandai penting
- Kirim WhatsApp

Target:

```text
○ Semua pengguna
○ Siswa
○ Wali murid
○ Kelas tertentu
```

---

# 23. Admin Dashboard

## KPI

```text
Total Siswa
Total Guru
Kehadiran Hari Ini
Notifikasi WA
Tugas Aktif
```

## Quick Actions

```text
+ Tambah Siswa
+ Tambah Guru
+ Buat Pengumuman
+ Import Excel
```

## System Health

```text
Website       ● Online
Database      ● Normal
WhatsApp      ● Normal
Backup        ● Hari ini
Storage       ● 72%
```

---

# 24. Data Master

Submenu:

```text
Data Master
├── Siswa
├── Guru
├── Orang Tua
├── Kelas
├── Mata Pelajaran
└── Tahun Ajaran
```

Table harus menyediakan:

- Search
- Filter
- Sort
- Pagination
- Import Excel
- Export

Action:

```text
Lihat
Edit
Nonaktifkan
Reset Password
```

---

# 25. Pengaturan

## Profil Sekolah

- Nama sekolah
- Logo
- Alamat
- Nomor telepon
- Email
- Website

## Tahun Ajaran

- Tahun ajaran
- Semester

## Absensi

- Jam buka
- Batas terlambat
- Durasi sesi
- QR refresh interval

## WhatsApp

- Template pesan
- Status layanan
- Retry
- Unsubscribe

## Tugas

- Bobot default
- Batas file
- Ukuran maksimum
- Format file

---

# 26. Maintenance

Halaman maintenance hanya dapat diakses admin yang memiliki hak sesuai PRD.

UI:

```text
Mode Maintenance

Status:
● Website Normal

[ Aktifkan Maintenance ]
```

Saat aktif:

```text
Website sedang dalam pemeliharaan.

Kami sedang melakukan peningkatan sistem.
Silakan kembali beberapa saat lagi.
```

Admin tetap dapat login.

---

# 27. System Log

Table:

| Waktu | User | Aktivitas | Modul | Status |
|---|---|---|---|---|

Contoh:

```text
09 Okt 2026 07:12
Guru: Ibu Sari
Mengubah status Budi
Absensi
Success
```

Filter:

- User
- Modul
- Status
- Date range

---

# 28. Component Library

## Button

Variants:

```text
Primary
Secondary
Outline
Ghost
Danger
```

States:

```text
Default
Hover
Focus
Disabled
Loading
```

## Input

Variants:

```text
Text
Password
Search
Select
Date
Time
File Upload
Textarea
```

## Badge

```text
Success
Warning
Error
Info
Neutral
```

## Card

Variants:

```text
Default
Interactive
Statistic
Announcement
Task
Attendance
```

## Modal

Digunakan untuk:

- Konfirmasi hapus
- Ubah status absensi
- Konfirmasi submit tugas
- Reset password
- Aktivasi maintenance

## Toast

Jenis:

```text
Success
Warning
Error
Info
```

---

# 29. Table UX

Desktop:

- Full table.

Mobile:

- Card list atau horizontal scroll.
- Kolom penting diprioritaskan.
- Action menggunakan menu `...`.

Jangan memaksakan tabel desktop menjadi tabel kecil di mobile.

---

# 30. Empty States

Contoh tugas kosong:

```text
Belum ada tugas

Belum ada tugas yang diberikan untuk kelas ini.

[ Kembali ke Dashboard ]
```

Contoh absensi:

```text
Belum ada sesi absensi

Guru belum membuka sesi absensi hari ini.
```

---

# 31. Loading State

Gunakan skeleton loading.

Contoh:

```text
┌───────────────────────┐
│ ███████████████       │
│ ████████              │
│                       │
│ █████████████         │
└───────────────────────┘
```

Hindari loading spinner penuh halaman kecuali proses autentikasi atau proses kritikal.

---

# 32. Error State

Contoh:

```text
Terjadi kesalahan

Data belum dapat dimuat.
Silakan coba lagi.

[ Coba Lagi ]
```

Pesan error harus:

- Jelas
- Singkat
- Tidak teknis
- Memberikan tindakan berikutnya

---

# 33. Accessibility

Target:

- Kontras teks mudah dibaca.
- Semua button memiliki label jelas.
- Form memiliki label.
- Keyboard navigation.
- Focus state terlihat.
- Jangan menggunakan warna sebagai satu-satunya indikator status.
- Status harus memiliki icon + text.

Contoh:

```text
✓ Hadir
⚠ Terlambat
✕ Alfa
```

---

# 34. Responsive Breakpoints

```text
Mobile:
320–767px

Tablet:
768–1023px

Desktop:
1024–1439px

Large Desktop:
1440px+
```

---

# 35. Responsive Behavior

## Sidebar

Desktop:

```text
240px
```

Tablet:

```text
72px collapsed
```

Mobile:

```text
Drawer
```

## Dashboard Cards

Desktop:

```text
4 columns
```

Tablet:

```text
2 columns
```

Mobile:

```text
1 column
```

## Form

Desktop:

```text
2 columns
```

Mobile:

```text
1 column
```

---

# 36. User Flow Utama

## Absensi

```text
Guru Login
↓
Dashboard Guru
↓
Pilih Kelas
↓
Buka Sesi Absensi
↓
QR Code / Tombol Absen
↓
Siswa Absen
↓
Sistem Validasi
↓
Status Kehadiran
↓
Queue WhatsApp
↓
Orang Tua Menerima Notifikasi
↓
Rekap Absensi
```

## Tugas

```text
Guru Login
↓
Buat Tugas
↓
Publikasikan
↓
Tugas Muncul di Dashboard Siswa
↓
Siswa Mengerjakan
↓
Submit
↓
Sistem Menilai
↓
Nilai Tersimpan
↓
Siswa & Orang Tua Melihat Nilai
```

---

# 37. Figma / Penpot Page Structure

Jika diterjemahkan ke Figma atau Penpot, gunakan struktur:

```text
01 — Cover
02 — Design Principles
03 — Colors
04 — Typography
05 — Icons
06 — Components
07 — Homepage
08 — Login
09 — Student
10 — Teacher
11 — Parent
12 — Admin
13 — Responsive
14 — Prototype
15 — Design Handoff
```

---

# 38. Screen List

## Public

- Homepage
- Profil
- Program
- Berita
- Pengumuman
- Kontak
- Login

## Student

- Dashboard
- Absensi
- Tugas
- Detail Tugas
- Pengerjaan Tugas
- Nilai
- Pengumuman
- Profile

## Teacher

- Dashboard
- Absensi
- Detail Absensi
- Rekap Absensi
- Tugas
- Buat Tugas
- Detail Tugas
- Penilaian
- Kelas Saya
- Pengumuman
- Profile

## Parent

- Dashboard
- Absensi Anak
- Detail Absensi
- Tugas Anak
- Nilai Anak
- Pengumuman
- Profile

## Admin

- Dashboard
- Data Siswa
- Data Guru
- Data Orang Tua
- Data Kelas
- Data Mata Pelajaran
- Absensi
- Rekap Absensi
- Tugas & Nilai
- Pengumuman
- Pengaturan
- Maintenance
- Backup
- System Log
- Profile

---

# 39. Prototype Priority

Prototype pertama sebaiknya fokus pada flow yang paling penting:

### Flow 1 — Absensi

```text
Login
→ Dashboard Guru
→ Absensi
→ Buka Sesi
→ QR
→ Siswa Scan
→ Status Hadir
→ Notifikasi Orang Tua
```

### Flow 2 — Tugas

```text
Login Guru
→ Buat Tugas
→ Publikasi
→ Login Siswa
→ Buka Tugas
→ Kerjakan
→ Submit
→ Nilai
```

### Flow 3 — Parent

```text
Login Orang Tua
→ Dashboard
→ Status Kehadiran
→ Riwayat
→ Tugas
→ Nilai
```

### Flow 4 — Admin

```text
Login Admin
→ Dashboard
→ Rekap Absensi
→ Export
→ Pengumuman
→ Pengaturan
```

---

# 40. Design Handoff

Setiap screen yang masuk development harus memiliki:

- Desktop design.
- Mobile design.
- Component states.
- Empty state.
- Loading state.
- Error state.
- Success state.
- Responsive behavior.
- Interaction specification.
- Form validation.
- Permission/role restriction.

Developer harus menggunakan component yang sama untuk elemen yang sama agar UI konsisten.

---

# 41. Definition of Done — UI/UX

Sebuah halaman dianggap selesai jika:

- [ ] Desktop selesai.
- [ ] Mobile selesai.
- [ ] Tablet behavior ditentukan.
- [ ] Semua component menggunakan design token.
- [ ] Hover state tersedia.
- [ ] Focus state tersedia.
- [ ] Disabled state tersedia.
- [ ] Loading state tersedia.
- [ ] Empty state tersedia.
- [ ] Error state tersedia.
- [ ] Success state tersedia.
- [ ] Accessibility dasar terpenuhi.
- [ ] Prototype interaction selesai.
- [ ] Developer handoff selesai.

---

# 42. Catatan Penting

PRD menetapkan bahwa keamanan dan privasi data siswa merupakan syarat utama. UI tidak boleh menampilkan data anak kepada akun yang tidak memiliki hak akses. Nomor WhatsApp orang tua juga hanya dapat dilihat oleh admin sesuai aturan akses.

Website juga harus responsif untuk HP dan menargetkan halaman utama termuat di bawah 3 detik pada jaringan 4G.

Detail keamanan backend, enkripsi penyimpanan, API, database, antrean WhatsApp, dan arsitektur teknis tidak didefinisikan secara visual di dokumen ini dan perlu diturunkan ke dokumen technical design terpisah.

---

# 43. Next Design Phase

Urutan implementasi desain:

```text
DESIGN SYSTEM
      ↓
COMPONENT LIBRARY
      ↓
HOMEPAGE
      ↓
LOGIN
      ↓
DASHBOARD SISWA
      ↓
DASHBOARD GURU
      ↓
ABSENSI
      ↓
TUGAS
      ↓
DASHBOARD ORANG TUA
      ↓
DASHBOARD ADMIN
      ↓
REKAP & REPORT
      ↓
PENGUMUMAN
      ↓
PENGATURAN
      ↓
RESPONSIVE
      ↓
PROTOTYPE
      ↓
DEVELOPER HANDOFF
```
