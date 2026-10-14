<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add the settings that make up the school profile (identity, logo, contact,
     * and social media) so existing installations gain the new fields.
     */
    public function up(): void
    {
        $settings = [
            ['key' => 'school_level', 'value' => 'SMA'],
            ['key' => 'school_accreditation', 'value' => 'A'],
            ['key' => 'school_motto', 'value' => 'Aman, Nyaman, dan Bermutu'],
            ['key' => 'school_description', 'value' => 'Sekolah yang berkomitmen menciptakan pengalaman belajar yang aman, nyaman, dan bermakna bagi setiap siswa.'],
            ['key' => 'school_city', 'value' => 'Nusantara'],
            ['key' => 'school_province', 'value' => null],
            ['key' => 'school_postal_code', 'value' => null],
            ['key' => 'school_whatsapp', 'value' => null],
            ['key' => 'school_website', 'value' => null],
            ['key' => 'school_logo_path', 'value' => null],
            ['key' => 'school_social_facebook', 'value' => null],
            ['key' => 'school_social_instagram', 'value' => null],
            ['key' => 'school_social_youtube', 'value' => null],
            ['key' => 'school_social_tiktok', 'value' => null],
            ['key' => 'school_social_twitter', 'value' => null],
        ];

        foreach ($settings as $setting) {
            DB::table('application_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => 'text',
                    'group' => 'school',
                    'created_at' => now(),
                    'updated_at' => now(),
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
            'school_level',
            'school_accreditation',
            'school_description',
            'school_motto',
            'school_city',
            'school_province',
            'school_postal_code',
            'school_whatsapp',
            'school_website',
            'school_logo_path',
            'school_social_facebook',
            'school_social_instagram',
            'school_social_youtube',
            'school_social_tiktok',
            'school_social_twitter',
        ])->delete();
    }
};
