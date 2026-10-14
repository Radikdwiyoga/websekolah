<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the default homepage content (hero, keunggulan, program) so the
     * public site keeps rendering while the content becomes editable.
     */
    public function up(): void
    {
        $advantages = [
            ['icon' => 'book', 'tone' => 'primary', 'title' => 'Pendidikan Berkualitas', 'description' => 'Kurikulum yang relevan, guru berdedikasi, dan proses belajar yang terukur.'],
            ['icon' => 'school', 'tone' => 'success', 'title' => 'Lingkungan Aman', 'description' => 'Sekolah yang bersih, nyaman, dan memprioritaskan keselamatan setiap siswa.'],
            ['icon' => 'grade', 'tone' => 'warning', 'title' => 'Pengembangan Karakter', 'description' => 'Pembiasaan budi pekerti dan karakter unggul dalam keseharian belajar.'],
            ['icon' => 'guardian', 'tone' => 'info', 'title' => 'Dukungan Orang Tua', 'description' => 'Dukungan penuh bagi orang tua untuk memantau perkembangan belajar anak.'],
        ];

        $programs = [
            ['icon' => 'book', 'tone' => 'primary', 'title' => 'Kegiatan Belajar Mengajar', 'description' => 'Jadwal pelajaran terstruktur dengan dukungan penilaian yang transparan.'],
            ['icon' => 'students', 'tone' => 'success', 'title' => 'Ekstrakurikuler', 'description' => 'Wadah pengembangan minat dan bakat siswa di luar jam pelajaran.'],
            ['icon' => 'grade', 'tone' => 'warning', 'title' => 'Pembinaan Karakter', 'description' => 'Pembiasaan kedisiplinan, akhlak mulia, dan kepemimpinan siswa.'],
            ['icon' => 'settings', 'tone' => 'info', 'title' => 'Layanan Digital', 'description' => 'Absensi, tugas, nilai, dan pengumuman dapat diakses dari mana saja.'],
        ];

        $settings = [
            ['key' => 'home_hero_title', 'value' => 'Bersama Membangun Generasi Masa Depan', 'type' => 'text'],
            ['key' => 'home_hero_subtitle', 'value' => 'Sekolah yang aman, nyaman, dan mendukung perkembangan potensi setiap siswa.', 'type' => 'text'],
            ['key' => 'home_advantages_heading', 'value' => 'Mengapa Memilih Sekolah Kami?', 'type' => 'text'],
            ['key' => 'home_advantages_description', 'value' => 'Kami memadukan pembelajaran bermutu dengan lingkungan yang aman dan dukungan penuh bagi orang tua.', 'type' => 'text'],
            ['key' => 'home_advantages', 'value' => json_encode($advantages), 'type' => 'json'],
            ['key' => 'home_programs_heading', 'value' => 'Program dan Layanan Sekolah', 'type' => 'text'],
            ['key' => 'home_programs_description', 'value' => null, 'type' => 'text'],
            ['key' => 'home_programs', 'value' => json_encode($programs), 'type' => 'json'],
        ];

        foreach ($settings as $setting) {
            DB::table('application_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => 'home',
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('application_settings')->whereIn('key', [
            'home_hero_title',
            'home_hero_subtitle',
            'home_advantages_heading',
            'home_advantages_description',
            'home_advantages',
            'home_programs_heading',
            'home_programs_description',
            'home_programs',
        ])->delete();
    }
};
