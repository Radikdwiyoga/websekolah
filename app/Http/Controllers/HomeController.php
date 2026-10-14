<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\SchoolAgenda;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\HomepageContentService;
use App\Services\SchoolProfileService;
use App\Services\SettingService;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the school's public homepage.
     */
    public function __invoke(
        SchoolProfileService $schoolProfile,
        SettingService $settings,
        HomepageContentService $homepage,
    ): View {
        return view('welcome', [
            'school' => $schoolProfile->all(),
            'home' => $homepage->all(),
            'agendas' => SchoolAgenda::query()
                ->published()
                ->orderBy('sort_order')
                ->orderByDesc('event_date')
                ->orderByDesc('id')
                ->limit(9)
                ->get(),
            'announcements' => Announcement::query()
                ->public()
                ->orderByDesc('is_important')
                ->latest('published_at')
                ->limit(3)
                ->get(),
            'stats' => [
                'students' => Student::query()->count(),
                'teachers' => Teacher::query()->count(),
                'classes' => SchoolClass::query()->count(),
            ],
            'maintenance' => (bool) $settings->get('maintenance_mode', false),
            'maintenanceMessage' => $settings->get(
                'maintenance_message',
                'Website sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
            ),
        ]);
    }
}
