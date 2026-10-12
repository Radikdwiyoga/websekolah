<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\SettingService;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the school's public homepage.
     */
    public function __invoke(SettingService $settings): View
    {
        $school = [
            'name' => $settings->get('school_name', config('app.name', 'Website Sekolah')),
            'npsn' => $settings->get('school_npsn'),
            'address' => $settings->get('school_address'),
            'phone' => $settings->get('school_phone'),
            'email' => $settings->get('school_email'),
            'headmaster' => $settings->get('headmaster_name'),
        ];

        return view('welcome', [
            'school' => $school,
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
