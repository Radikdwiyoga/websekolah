<?php

namespace App\Services;

use App\Enums\SettingType;
use App\Enums\SystemLogStatus;
use App\Models\SystemLog;
use App\Models\User;

class HomepageContentService
{
    /**
     * Icons selectable when building keunggulan / program cards. Limited to the
     * icons shipped with the x-icon component.
     *
     * @var array<int, string>
     */
    public const ICONS = [
        'book',
        'school',
        'grade',
        'guardian',
        'students',
        'settings',
        'attendance',
        'task',
        'bell',
        'calendar',
        'report',
        'announcement',
        'profile',
        'check',
        'info',
    ];

    /**
     * Friendly labels shown next to each icon in the homepage editor.
     *
     * @var array<string, string>
     */
    public const ICON_LABELS = [
        'book' => 'Buku / Pelajaran',
        'school' => 'Sekolah',
        'grade' => 'Bintang / Prestasi',
        'guardian' => 'Orang Tua',
        'students' => 'Siswa',
        'settings' => 'Layanan / Pengaturan',
        'attendance' => 'Absensi',
        'task' => 'Tugas',
        'bell' => 'Notifikasi',
        'calendar' => 'Kalender',
        'report' => 'Laporan',
        'announcement' => 'Pengumuman',
        'profile' => 'Profil',
        'check' => 'Centang',
        'info' => 'Informasi',
    ];

    /**
     * Colour tones selectable for the feature cards.
     *
     * @var array<string, string>
     */
    public const TONES = [
        'primary' => 'Biru',
        'success' => 'Hijau',
        'warning' => 'Kuning',
        'danger' => 'Merah',
        'info' => 'Biru Muda',
    ];

    /**
     * Fallback content used when a setting has never been filled.
     *
     * @var array<string, mixed>
     */
    private const DEFAULTS = [
        'hero_title' => 'Bersama Membangun Generasi Masa Depan',
        'hero_subtitle' => 'Sekolah yang aman, nyaman, dan mendukung perkembangan potensi setiap siswa.',
        'advantages_heading' => 'Mengapa Memilih Sekolah Kami?',
        'advantages_description' => 'Kami memadukan pembelajaran bermutu dengan lingkungan yang aman dan dukungan penuh bagi orang tua.',
        'advantages' => [],
        'programs_heading' => 'Program dan Layanan Sekolah',
        'programs_description' => null,
        'programs' => [],
    ];

    public function __construct(private readonly SettingService $settings) {}

    /**
     * The editable homepage content, ready for the public view and the form.
     *
     * @return array{
     *     hero_title: string,
     *     hero_subtitle: string,
     *     advantages_heading: string,
     *     advantages_description: string|null,
     *     advantages: array<int, array<string, string>>,
     *     programs_heading: string,
     *     programs_description: string|null,
     *     programs: array<int, array<string, string>>,
     * }
     */
    public function all(): array
    {
        return [
            'hero_title' => (string) $this->settings->get('home_hero_title', self::DEFAULTS['hero_title']),
            'hero_subtitle' => (string) $this->settings->get('home_hero_subtitle', self::DEFAULTS['hero_subtitle']),
            'advantages_heading' => (string) $this->settings->get('home_advantages_heading', self::DEFAULTS['advantages_heading']),
            'advantages_description' => $this->settings->get('home_advantages_description', self::DEFAULTS['advantages_description']),
            'advantages' => $this->items('home_advantages'),
            'programs_heading' => (string) $this->settings->get('home_programs_heading', self::DEFAULTS['programs_heading']),
            'programs_description' => $this->settings->get('home_programs_description', self::DEFAULTS['programs_description']),
            'programs' => $this->items('home_programs'),
        ];
    }

    /**
     * Persist the homepage content.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, User $actor): void
    {
        $this->settings->set('home_hero_title', $data['hero_title'] ?? null, 'home');
        $this->settings->set('home_hero_subtitle', $data['hero_subtitle'] ?? null, 'home');
        $this->settings->set('home_advantages_heading', $data['advantages_heading'] ?? null, 'home');
        $this->settings->set('home_advantages_description', $data['advantages_description'] ?? null, 'home');
        $this->settings->set('home_programs_heading', $data['programs_heading'] ?? null, 'home');
        $this->settings->set('home_programs_description', $data['programs_description'] ?? null, 'home');

        $this->settings->set('home_advantages', $this->normalise($data['advantages'] ?? []), 'home', SettingType::Json);
        $this->settings->set('home_programs', $this->normalise($data['programs'] ?? []), 'home', SettingType::Json);

        SystemLog::query()->create([
            'user_id' => $actor->id,
            'module' => 'settings',
            'action' => 'update',
            'description' => 'Memperbarui konten halaman utama.',
            'status' => SystemLogStatus::Success,
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function items(string $key): array
    {
        $value = $this->settings->get($key, self::DEFAULTS[$key === 'home_advantages' ? 'advantages' : 'programs']);

        if (! is_array($value)) {
            return [];
        }

        return $this->normalise($value);
    }

    /**
     * Keep only valid, non-empty cards with a consistent shape.
     *
     * @param  array<int, mixed>  $items
     * @return array<int, array<string, string>>
     */
    private function normalise(array $items): array
    {
        $normalised = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $icon = (string) ($item['icon'] ?? 'check');
            $tone = (string) ($item['tone'] ?? 'primary');

            $normalised[] = [
                'icon' => in_array($icon, self::ICONS, true) ? $icon : 'check',
                'tone' => array_key_exists($tone, self::TONES) ? $tone : 'primary',
                'title' => $title,
                'description' => trim((string) ($item['description'] ?? '')),
            ];
        }

        return $normalised;
    }
}
