# PRD Website Sekolah

Oct 9, 2026 · @Radik

Website sekolah ini mencatat absensi siswa, langsung mengabari orang tua lewat WhatsApp begitu siswa absen, dan mengelola tugas rumah yang diunggah serta dinilai secara otomatis.

## Latar belakang dan tujuan

Absensi manual di buku atau lembar kertas lambat direkap, orang tua baru tahu anaknya tidak masuk setelah dihubungi wali kelas, dan tugas rumah dikumpulkan serta dikoreksi satu per satu. Website ini menyatukan ketiganya dalam satu sistem.

| Tujuan | Target terukur |
| --- | --- |
| Absensi tercatat digital | 95% kelas memakai absensi digital dalam 1 bulan setelah rilis |
| Orang tua cepat tahu status kehadiran | 90% notifikasi WA terkirim dalam 2 menit setelah absen |
| Beban koreksi guru berkurang | 70% tugas pilihan ganda dan isian singkat dinilai otomatis tanpa campur tangan guru |
| Rekap tidak lagi manual | Rekap absensi bulanan keluar dalam 1 klik, tanpa input ulang |

## Pengguna dan peran

Empat peran memakai sistem ini, masing-masing dengan hak akses berbeda.

| Peran | Yang dilakukan di sistem | Hak akses utama |
| --- | --- | --- |
| Siswa | Absen, melihat dan mengerjakan tugas, melihat nilai | Hanya data dirinya sendiri |
| Guru | Membuka sesi absen, membuat tugas, menentukan kunci jawaban, mengoreksi tugas esai | Kelas dan mata pelajaran yang diampu |
| Orang tua | Menerima notifikasi WA, melihat riwayat absensi dan nilai anak | Hanya data anak yang terdaftar |
| Admin sekolah | Mengelola akun, kelas, jadwal, nomor WA orang tua, dan rekap; menerbitkan pengumuman program sekolah; mengatur aplikasi; menjalankan maintenance website | Seluruh data sekolah |

## Ruang lingkup

Rilis pertama fokus pada tiga fitur inti saja. Hal lain ditunda supaya jadwal tidak melebar.

| Masuk rilis pertama | Belum masuk |
| --- | --- |
| Login per peran (siswa, guru, orang tua, admin) | Pembayaran SPP |
| Absensi harian per kelas | Absensi per mata pelajaran dan per jam |
| Notifikasi WA ke orang tua saat siswa absen | Chat dua arah dengan orang tua |
| Tugas rumah: upload, penilaian otomatis, rekap nilai | Rapor digital dan e-learning penuh |
| Rekap absensi dan nilai (unduh Excel/PDF); panel admin untuk pengumuman, pengaturan, dan maintenance | Aplikasi mobile native |

## Fitur 1: Absensi

Guru membuka sesi absen, siswa melakukan absen dalam jendela waktu yang ditentukan, dan guru bisa mengoreksi hasilnya.

**Alur utama**

1. Guru membuka sesi absen untuk kelasnya dan menentukan batas waktu (misalnya 07.00 sampai 07.30).
2. Siswa login dan menekan tombol absen, atau memindai QR code kelas.
3. Sistem mencatat status: hadir, terlambat (lewat batas), atau belum absen.
4. Setelah sesi ditutup, siswa yang belum absen ditandai alfa, kecuali guru mengubahnya menjadi sakit atau izin.
5. Rekap harian dan bulanan tersedia untuk guru dan admin.

| Kode | Kebutuhan | Prioritas |
| --- | --- | --- |
| ABS-1 | Guru membuka dan menutup sesi absen per kelas | Wajib |
| ABS-2 | Siswa absen lewat tombol atau QR code, satu kali per sesi | Wajib |
| ABS-3 | Validasi anti-titip absen: QR berganti tiap 30 detik, atau batasan lokasi sekolah | Wajib |
| ABS-4 | Guru mengubah status manual (sakit, izin, alfa) disertai catatan | Wajib |
| ABS-5 | Siswa mengajukan izin dengan unggah surat atau foto, disetujui guru | Sebaiknya ada |
| ABS-6 | Rekap harian, mingguan, bulanan; unduh Excel dan PDF | Wajib |
| ABS-7 | Log perubahan status (siapa, kapan, dari apa ke apa) | Wajib |

## Fitur 2: Notifikasi WhatsApp ke orang tua

Setiap kali status kehadiran siswa tercatat, orang tua menerima pesan WA otomatis. Pengiriman berjalan di latar belakang lewat antrean, jadi absensi tidak melambat kalau layanan WA sedang bermasalah.

**Pemicu dan isi pesan**

| Pemicu | Isi pesan (contoh) |
| --- | --- |
| Siswa berhasil absen hadir | "Ananda Budi (7A) telah hadir di sekolah pukul 06.52." |
| Siswa terlambat | "Ananda Budi (7A) tercatat terlambat, absen pukul 07.41." |
| Sesi ditutup dan siswa belum absen | "Ananda Budi (7A) belum tercatat hadir hari ini. Mohon konfirmasi ke wali kelas." |
| Guru mengubah status jadi sakit atau izin | "Status kehadiran Ananda Budi hari ini diubah menjadi izin oleh wali kelas." |

**Aturan kirim**

- Satu pesan per siswa per status; perubahan status mengirim pesan baru, bukan duplikat.
- Gagal kirim dicoba ulang maksimal 3 kali dengan jeda bertahap, lalu ditandai gagal dan muncul di dashboard admin.
- Admin bisa melihat log pengiriman: nomor tujuan, waktu, status terkirim atau gagal.
- Orang tua bisa berhenti berlangganan notifikasi lewat balasan tertentu, dan pilihan itu dihormati sistem.
- Nomor WA orang tua diisi admin atau saat pendaftaran, diverifikasi sekali, dan dapat diubah hanya oleh admin.

## Fitur 3: Tugas rumah, upload dan penilaian otomatis

Guru membuat tugas sekali, siswa mengerjakan atau mengunggah jawaban, lalu sistem menilai otomatis sesuai jenis soalnya. Soal esai tetap dikoreksi guru, dengan bantuan skor awal bila diaktifkan.

**Alur utama**

1. Guru membuat tugas: judul, kelas, tenggat, jenis soal, bobot nilai, dan kunci jawaban.
2. Tugas muncul otomatis di dashboard siswa, dan siswa mendapat pengingat sebelum tenggat.
3. Siswa menjawab langsung di website atau mengunggah file (PDF, DOCX, JPG, PNG; maksimal 10 MB).
4. Sistem menilai begitu jawaban dikirim: pilihan ganda dan isian singkat dicocokkan dengan kunci jawaban.
5. Nilai tersimpan otomatis ke rekap, dan siswa serta orang tua dapat melihatnya.
6. Jawaban esai atau file unggahan masuk antrean guru untuk dinilai manual.

| Jenis tugas | Cara penilaian | Peran guru |
| --- | --- | --- |
| Pilihan ganda | Otomatis, cocok dengan kunci jawaban | Hanya membuat soal dan kunci |
| Isian singkat | Otomatis, cocok dengan daftar jawaban benar (tidak peka huruf besar-kecil) | Menyiapkan variasi jawaban yang benar |
| Esai | Manual oleh guru; opsional skor awal otomatis berbasis AI | Meninjau dan memberi nilai akhir |
| Unggah file | Manual oleh guru, dengan rubrik | Menilai sesuai rubrik |

| Kode | Kebutuhan | Prioritas |
| --- | --- | --- |
| TGS-1 | Guru membuat, mengubah, dan menutup tugas per kelas | Wajib |
| TGS-2 | Siswa mengunggah jawaban sebelum tenggat; pengiriman ulang diizinkan sampai tenggat | Wajib |
| TGS-3 | Penilaian otomatis untuk pilihan ganda dan isian singkat | Wajib |
| TGS-4 | Pengiriman terlambat ditandai, dengan pengurangan nilai yang bisa diatur guru | Wajib |
| TGS-5 | Guru dapat mengubah nilai otomatis dan menambahkan komentar | Wajib |
| TGS-6 | Rekap nilai per siswa dan per tugas, unduh Excel | Wajib |
| TGS-7 | Deteksi kemiripan jawaban antarsiswa untuk esai | Nanti |

## Fitur 4: Panel admin, informasi sekolah, dan pengaturan

Admin mengelola website tanpa bantuan developer: menerbitkan informasi program sekolah untuk siswa dan wali murid, mengatur perilaku aplikasi, dan menjalankan maintenance. Wali murid di sini sama dengan akun orang tua.

| Kode | Kebutuhan | Prioritas |
| --- | --- | --- |
| ADM-1 | Admin membuat, mengubah, menjadwalkan, dan menghapus pengumuman program sekolah (kegiatan, libur, ujian, rapat wali murid) | Wajib |
| ADM-2 | Pengumuman bisa ditujukan ke semua pengguna, siswa saja, wali murid saja, atau kelas tertentu | Wajib |
| ADM-3 | Pengumuman bisa dilengkapi lampiran (gambar atau PDF) dan ditandai penting | Wajib |
| ADM-4 | Siswa dan wali murid melihat pengumuman di dashboard, dengan penanda belum dibaca | Wajib |
| ADM-5 | Pengumuman penting bisa ikut dikirim lewat WA memakai antrean yang sama dengan notifikasi absensi | Sebaiknya ada |
| ADM-6 | Pengaturan aplikasi: profil sekolah, tahun ajaran dan semester, daftar kelas, jam buka absen dan batas terlambat, template pesan WA, bobot nilai bawaan, batas jenis dan ukuran file unggahan | Wajib |
| ADM-7 | Manajemen akun: tambah, nonaktifkan, reset kata sandi, dan impor siswa serta nomor WA wali murid lewat Excel | Wajib |
| ADM-8 | Mode maintenance: pengguna melihat halaman pemberitahuan, admin tetap bisa masuk | Wajib |
| ADM-9 | Pencadangan data manual dan unduh cadangan, ditambah pencadangan otomatis harian | Wajib |
| ADM-10 | Halaman log sistem: kegagalan notifikasi WA, galat aplikasi, dan riwayat perubahan pengaturan (siapa, kapan, apa) | Wajib |
| ADM-11 | Hak akses admin bertingkat: super admin (semua pengaturan dan maintenance) dan admin operasional (pengumuman, akun, rekap) | Sebaiknya ada |

## Kebutuhan non-fungsional

Data siswa adalah data anak di bawah umur, jadi keamanan dan privasi menjadi syarat utama, bukan tambahan.

| Aspek | Kebutuhan |
| --- | --- |
| Keamanan | Login dengan kata sandi ter-hash, sesi berbatas waktu, akses berbasis peran, HTTPS di semua halaman |
| Privasi | Orang tua hanya melihat data anaknya; nomor WA hanya terlihat oleh admin; mengikuti UU Pelindungan Data Pribadi (UU PDP) |
| Performa | Halaman utama termuat di bawah 3 detik di jaringan 4G; mampu melayani 500 siswa absen bersamaan dalam jendela 15 menit |
| Ketersediaan | Target 99% pada jam sekolah (06.00 sampai 17.00); pencadangan basis data harian |
| Perangkat | Responsif untuk HP, karena sebagian besar siswa dan orang tua mengakses dari HP |
| Audit | Semua perubahan nilai dan status absensi tercatat dengan pelaku dan waktu |
| Penyimpanan file | Berkas tugas disimpan terenkripsi dan dipindai ekstensi serta ukurannya sebelum diterima |

## Arsitektur usulan dan model data

&#91;embedded content: arsitektur usulan · 7 komponen\]

Website hanya bicara dengan API. API menyimpan data ke database, berkas ke penyimpanan file, menjalankan penilaian otomatis, dan menaruh pesan WA ke antrean yang dikirim terpisah.

| Data | Isi pokok | Relasi |
| --- | --- | --- |
| Pengguna | Nama, peran, kata sandi ter-hash | Satu pengguna punya satu peran |
| Siswa | NIS, kelas, nomor WA orang tua | Terhubung ke akun siswa dan akun orang tua |
| Sesi absen | Kelas, tanggal, jam buka dan tutup | Dibuat guru, punya banyak catatan absen |
| Catatan absen | Siswa, status, waktu, diubah oleh | Milik satu sesi dan satu siswa |
| Tugas | Judul, kelas, tenggat, jenis soal, kunci jawaban | Dibuat guru, punya banyak pengumpulan |
| Pengumpulan | Siswa, jawaban atau file, waktu kirim, nilai | Milik satu tugas dan satu siswa |
| Log notifikasi | Tujuan, isi, status, jumlah percobaan | Terkait catatan absen |

## Tahapan rilis

Pengembangan dibagi tiga fase supaya sekolah bisa mulai memakai absensi lebih dulu. Estimasi minggu di bawah masih usulan dan perlu disesuaikan dengan ukuran tim.

| Fase | Isi | Kriteria selesai | Estimasi (usulan) |
| --- | --- | --- | --- |
| 1. Fondasi dan absensi | Login per peran, manajemen kelas dan siswa, absensi, rekap, pengaturan aplikasi, pengumuman sekolah | Satu kelas percobaan memakai absensi digital seminggu penuh tanpa kendala | 4 minggu |
| 2. Notifikasi WhatsApp | Antrean pesan, integrasi layanan WA, log pengiriman, opsi berhenti berlangganan | Notifikasi terkirim ke orang tua kelas percobaan, tingkat gagal kirim di bawah 5% | 3 minggu |
| 3. Tugas rumah | Pembuatan tugas, unggah jawaban, penilaian otomatis, rekap nilai | Satu mata pelajaran menjalankan satu siklus tugas dari pembuatan sampai nilai tanpa input manual | 5 minggu |
| 4. Uji dan peluncuran | Mode maintenance, pencadangan, log sistem, uji beban, uji keamanan, pelatihan guru dan admin, rilis bertahap per kelas | Seluruh guru terlatih, tidak ada temuan keamanan tingkat tinggi yang terbuka | 2 minggu |

## Risiko dan pertanyaan terbuka

| Risiko | Dampak | Penanganan |
| --- | --- | --- |
| Layanan WA tidak resmi diblokir atau berubah aturan | Notifikasi berhenti | Pakai WhatsApp Business API resmi, siapkan cadangan SMS atau email |
| Siswa titip absen | Data kehadiran tidak valid | QR berganti berkala, batasan lokasi, log perubahan status |
| Nomor WA orang tua salah atau berganti | Pesan salah sasaran | Verifikasi sekali saat pendaftaran, hanya admin yang bisa mengubah |
| Guru kesulitan beralih dari cara manual | Adopsi rendah | Pelatihan singkat, rilis bertahap per kelas, panduan bergambar |
| Kebocoran data siswa | Pelanggaran privasi | Akses berbasis peran, enkripsi, audit log, pencadangan rutin |

**Pertanyaan yang masih terbuka**

- [ ] Jenjang dan jumlah siswa sekolah ini, untuk menentukan kapasitas dan lisensi layanan WA.
- [ ] Absensi hanya harian per kelas, atau nanti per mata pelajaran?
- [ ] Layanan WA yang dipakai: API resmi berbayar atau gateway pihak ketiga?
- [ ] Apakah esai akan dibantu skor awal berbasis AI, atau murni dinilai guru?
- [ ] Siapa yang memegang akun admin dan bertanggung jawab atas data nomor orang tua?
