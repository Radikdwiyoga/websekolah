<?php

namespace App\Http\Controllers;

use App\Enums\SettingType;
use App\Enums\SystemLogStatus;
use App\Models\ApplicationSetting;
use App\Models\SystemLog;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    /**
     * Friendly labels for the setting groups shown on the settings index.
     *
     * @var array<string, string>
     */
    private const GROUP_LABELS = [
        'school' => 'Profil Sekolah',
        'home' => 'Halaman Utama',
        'account' => 'Akun Pengguna',
        'student' => 'Siswa',
        'attendance' => 'Absensi',
        'grading' => 'Penilaian',
        'whatsapp' => 'Notifikasi WhatsApp',
        'upload' => 'Unggahan Berkas',
        'maintenance' => 'Pemeliharaan',
        'system' => 'Sistem',
        'general' => 'Umum',
    ];

    /**
     * Friendly labels for individual well-known settings.
     *
     * @var array<string, string>
     */
    private const SETTING_LABELS = [
        'attendance_open_time' => 'Jam Buka Absensi',
        'attendance_close_time' => 'Jam Tutup Absensi',
        'attendance_late_after' => 'Batas Waktu Terlambat',
        'default_grade_weight' => 'Bobot Nilai Default',
        'default_user_password' => 'Kata Sandi Default Guru & Orang Tua',
        'student_default_password' => 'Kata Sandi Default Siswa',
        'maintenance_mode' => 'Mode Pemeliharaan',
        'maintenance_message' => 'Pesan Pemeliharaan',
        'whatsapp_message_template' => 'Template Pesan WhatsApp',
        'upload_max_size_kb' => 'Ukuran Maksimal Berkas (KB)',
        'upload_allowed_extensions' => 'Ekstensi Berkas Diizinkan',
        'home_hero_title' => 'Judul Halaman Utama',
        'home_hero_subtitle' => 'Subjudul Halaman Utama',
        'home_advantages_heading' => 'Judul Keunggulan',
        'home_advantages_description' => 'Deskripsi Keunggulan',
        'home_advantages' => 'Daftar Keunggulan',
        'home_programs_heading' => 'Judul Program',
        'home_programs_description' => 'Deskripsi Program',
        'home_programs' => 'Daftar Program',
    ];

    public function __construct(private readonly SettingService $settings) {}

    /**
     * Halaman utama pengaturan — daftar semua pengaturan yang dikelompokkan.
     */
    public function index(): View
    {
        $grouped = $this->settings->all()
            ->groupBy->group
            ->sortKeys()
            ->map(fn (Collection $items) => $items->sortBy->key);

        return view('admin.settings.index', [
            'settings' => $grouped,
            'groupLabels' => self::GROUP_LABELS,
            'labels' => self::SETTING_LABELS,
        ]);
    }

    /**
     * Tampilkan form edit pengaturan berdasarkan key.
     */
    public function edit(string $key): View
    {
        $setting = ApplicationSetting::query()->where('key', $key)->firstOrFail();

        return view('admin.settings.edit', [
            'key' => $key,
            'name' => self::SETTING_LABELS[$key] ?? Str::headline($key),
            'type' => $setting->type ?? SettingType::Text,
            'current_value' => $setting->typedValue(),
            'group' => $setting->group ?? 'general',
        ]);
    }

    /**
     * Simpan perubahan pengaturan.
     */
    public function update(Request $request, string $key): RedirectResponse
    {
        $setting = ApplicationSetting::query()->where('key', $key)->firstOrFail();

        $validated = $request->validate([
            'value' => match ($setting->type) {
                SettingType::Integer => ['nullable', 'integer'],
                SettingType::Boolean => ['nullable', Rule::in(['0', '1'])],
                SettingType::Json => ['nullable', 'json'],
                default => ['nullable', 'string', 'max:5000'],
            },
        ]);

        $this->settings->set($key, $validated['value'] ?? null, $setting->group);

        SystemLog::query()->create([
            'user_id' => $request->user()?->id,
            'module' => 'settings',
            'action' => 'update',
            'description' => "Pengaturan {$key} diperbarui.",
            'status' => SystemLogStatus::Success,
        ]);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', "Pengaturan {$key} berhasil diperbarui.");
    }
}
