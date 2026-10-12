<?php

namespace App\Http\Controllers;

use App\Enums\SystemLogStatus;
use App\Models\ApplicationSetting;
use App\Models\SystemLog;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class AdminMaintenanceController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    /**
     * Display the maintenance management page.
     */
    public function index(): View
    {
        $maintenance = (bool) $this->settings->get('maintenance_mode', false);
        $message = $this->settings->get('maintenance_message', 'Website sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.');

        return view('admin.maintenance.index', [
            'maintenance' => $maintenance,
            'message' => $message,
        ]);
    }

    /**
     * Toggle maintenance mode on or off.
     */
    public function toggle(Request $request): RedirectResponse
    {
        $current = (bool) $this->settings->get('maintenance_mode', false);
        $newState = ! $current;

        ApplicationSetting::query()->updateOrCreate(
            ['key' => 'maintenance_mode'],
            ['value' => $newState ? '1' : '0', 'group' => 'system']
        );

        if ($request->filled('message')) {
            ApplicationSetting::query()->updateOrCreate(
                ['key' => 'maintenance_message'],
                ['value' => $request->input('message'), 'group' => 'system']
            );
        }

        SystemLog::query()->create([
            'user_id' => $request->user()?->id,
            'module' => 'system',
            'action' => 'maintenance_toggle',
            'description' => $newState ? 'Mode pemeliharaan diaktifkan' : 'Mode pemeliharaan dinonaktifkan',
            'status' => SystemLogStatus::Success,
        ]);

        $statusText = $newState ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.maintenance.index')
            ->with('success', "Mode pemeliharaan berhasil {$statusText}.");
    }

    /**
     * Clear application cache and compiled views.
     */
    public function clearCache(Request $request): RedirectResponse
    {
        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        SystemLog::query()->create([
            'user_id' => $request->user()?->id,
            'module' => 'system',
            'action' => 'cache_clear',
            'description' => 'Cache aplikasi dan view dibersihkan',
            'status' => SystemLogStatus::Success,
        ]);

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Cache aplikasi berhasil dibersihkan.');
    }
}
