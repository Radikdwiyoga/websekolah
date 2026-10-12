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
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    /**
     * Halaman utama pengaturan — daftar semua pengaturan terrygrup.
     */
    public function index(): View
    {
        $all = $this->settings->all();

        // Group by 'group' column, sort groups alphabetically, settings within each group by key
        $grouped = $all->groupBy->group->sortKeys()->map(fn (Collection $items) => $items->sortBy->key);

        return view('admin.settings.index', [
            'settings' => $grouped,
            'groups' => $grouped->keys()->values()->all(),
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
            'name' => $setting->name ?? ucfirst(str_replace('_', ' ', $key)),
            'type' => $setting->type ?? SettingType::String,
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

        $newValue = $request->input('value', $setting->value);
        $setting->value = $newValue;
        $setting->save();

        // Audit log
        SystemLog::query()->create([
            'user_id' => auth()->id(),
            'module' => 'settings',
            'action' => 'update',
            'description' => "Pengaturan {$setting->key} diubah menjadi: {$newValue}",
            'status' => SystemLogStatus::Success,
        ]);

        return redirect()->route('admin.settings.index')->with('success', "Pengaturan {$setting->key} berhasil diperbarui.");
    }
}
