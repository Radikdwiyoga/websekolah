<?php

namespace App\Services;

use App\Enums\SystemLogStatus;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SchoolProfileService
{
    /**
     * Setting keys that make up the editable school profile.
     *
     * @var array<int, string>
     */
    private const PROFILE_KEYS = [
        'school_name',
        'school_npsn',
        'school_level',
        'school_accreditation',
        'school_motto',
        'school_description',
        'school_address',
        'school_city',
        'school_province',
        'school_postal_code',
        'school_phone',
        'school_whatsapp',
        'school_email',
        'school_website',
        'headmaster_name',
    ];

    /**
     * Social media platforms editable from the school profile page.
     *
     * @var array<int, string>
     */
    private const SOCIAL_PLATFORMS = ['facebook', 'instagram', 'youtube', 'tiktok', 'twitter'];

    public function __construct(private readonly SettingService $settings) {}

    /**
     * The full school profile, ready for display in views.
     *
     * @return array{
     *     name: string,
     *     npsn: string|null,
     *     level: string|null,
     *     accreditation: string|null,
     *     motto: string|null,
     *     description: string|null,
     *     address: string|null,
     *     city: string|null,
     *     province: string|null,
     *     postal_code: string|null,
     *     phone: string|null,
     *     whatsapp: string|null,
     *     email: string|null,
     *     website: string|null,
     *     headmaster: string|null,
     *     logo_url: string|null,
     *     social: array<string, string|null>,
     * }
     */
    public function all(): array
    {
        return [
            'name' => (string) $this->settings->get('school_name', config('app.name', 'Website Sekolah')),
            'npsn' => $this->settings->get('school_npsn'),
            'level' => $this->settings->get('school_level'),
            'accreditation' => $this->settings->get('school_accreditation'),
            'motto' => $this->settings->get('school_motto'),
            'description' => $this->settings->get('school_description'),
            'address' => $this->settings->get('school_address'),
            'city' => $this->settings->get('school_city'),
            'province' => $this->settings->get('school_province'),
            'postal_code' => $this->settings->get('school_postal_code'),
            'phone' => $this->settings->get('school_phone'),
            'whatsapp' => $this->settings->get('school_whatsapp'),
            'email' => $this->settings->get('school_email'),
            'website' => $this->settings->get('school_website'),
            'headmaster' => $this->settings->get('headmaster_name'),
            'logo_url' => $this->settings->publicUrl('school_logo_path'),
            'social' => $this->social(),
        ];
    }

    /**
     * The logo URL, or null when no logo has been uploaded.
     */
    public function logoUrl(): ?string
    {
        return $this->settings->publicUrl('school_logo_path');
    }

    /**
     * Persist the school profile and optionally replace or remove the logo.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, ?UploadedFile $logo, bool $removeLogo, User $actor): void
    {
        if ($logo !== null) {
            $this->deleteLogo();
            $this->settings->set('school_logo_path', $logo->store('school', 'public'), 'school');
        } elseif ($removeLogo) {
            $this->deleteLogo();
            $this->settings->set('school_logo_path', null, 'school');
        }

        foreach (self::PROFILE_KEYS as $key) {
            if (array_key_exists($key, $data)) {
                $this->settings->set($key, $data[$key], 'school');
            }
        }

        foreach (self::SOCIAL_PLATFORMS as $platform) {
            $key = 'school_social_'.$platform;

            if (array_key_exists($key, $data)) {
                $this->settings->set($key, $data[$key], 'school');
            }
        }

        SystemLog::query()->create([
            'user_id' => $actor->id,
            'module' => 'settings',
            'action' => 'update',
            'description' => 'Memperbarui profil sekolah '.$this->settings->get('school_name'),
            'status' => SystemLogStatus::Success,
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function social(): array
    {
        $social = [];

        foreach (self::SOCIAL_PLATFORMS as $platform) {
            $social[$platform] = $this->settings->get('school_social_'.$platform);
        }

        return $social;
    }

    private function deleteLogo(): void
    {
        $path = $this->settings->get('school_logo_path');

        if (is_string($path) && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }
}
