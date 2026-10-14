<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'school_name' => ['required', 'string', 'max:150'],
            'school_npsn' => ['nullable', 'string', 'max:20'],
            'school_level' => ['nullable', 'string', 'max:30'],
            'school_accreditation' => ['nullable', 'string', 'max:10'],
            'school_motto' => ['nullable', 'string', 'max:255'],
            'school_description' => ['nullable', 'string', 'max:2000'],
            'school_address' => ['nullable', 'string', 'max:255'],
            'school_city' => ['nullable', 'string', 'max:100'],
            'school_province' => ['nullable', 'string', 'max:100'],
            'school_postal_code' => ['nullable', 'string', 'max:10'],
            'school_phone' => ['nullable', 'string', 'max:30'],
            'school_whatsapp' => ['nullable', 'string', 'max:30'],
            'school_email' => ['nullable', 'email', 'max:150'],
            'school_website' => ['nullable', 'url', 'max:255'],
            'headmaster_name' => ['nullable', 'string', 'max:150'],

            'school_social_facebook' => ['nullable', 'url', 'max:255'],
            'school_social_instagram' => ['nullable', 'url', 'max:255'],
            'school_social_youtube' => ['nullable', 'url', 'max:255'],
            'school_social_tiktok' => ['nullable', 'url', 'max:255'],
            'school_social_twitter' => ['nullable', 'url', 'max:255'],

            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'school_name' => 'nama sekolah',
            'school_npsn' => 'NPSN',
            'school_level' => 'jenjang sekolah',
            'school_accreditation' => 'akreditasi',
            'school_motto' => 'motto sekolah',
            'school_description' => 'deskripsi sekolah',
            'school_address' => 'alamat sekolah',
            'school_city' => 'kota/kabupaten',
            'school_province' => 'provinsi',
            'school_postal_code' => 'kode pos',
            'school_phone' => 'telepon sekolah',
            'school_whatsapp' => 'nomor WhatsApp sekolah',
            'school_email' => 'email sekolah',
            'school_website' => 'website sekolah',
            'headmaster_name' => 'nama kepala sekolah',
            'school_social_facebook' => 'tautan Facebook',
            'school_social_instagram' => 'tautan Instagram',
            'school_social_youtube' => 'tautan YouTube',
            'school_social_tiktok' => 'tautan TikTok',
            'school_social_twitter' => 'tautan X (Twitter)',
            'logo' => 'logo sekolah',
        ];
    }
}
