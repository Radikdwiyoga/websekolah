<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Services\AttendanceRecapService;
use App\Services\DashboardService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceRecapService $recaps,
        private readonly DashboardService $dashboard,
        private readonly SettingService $settings,
    ) {}

    /**
     * Display today's attendance overview for all classes.
     */
    public function index(): View
    {
        $activeYear = SchoolYear::query()->where('is_active', true)->first();

        return view('attendance.admin.index', [
            'activeYear' => $activeYear,
            'attendanceToday' => $this->dashboard->admin()['attendanceToday'],
            'openTime' => $this->settings->get('attendance_open_time', '07:00'),
            'closeTime' => $this->settings->get('attendance_close_time', '09:00'),
            'lateAfter' => $this->settings->get('attendance_late_after', '07:15'),
        ]);
    }

    /**
     * Display the attendance recap for every class in the active school year.
     */
    public function recap(Request $request): View
    {
        return view('attendance.admin.recap', [
            'recap' => $this->recaps->build($this->classIds(), $this->filters($request)),
        ]);
    }

    /**
     * Download the attendance recap as a CSV file.
     */
    public function recapExport(Request $request): StreamedResponse
    {
        return $this->recaps->download($this->recaps->build($this->classIds(), $this->filters($request)));
    }

    /**
     * @return array<int, int>
     */
    private function classIds(): array
    {
        $yearId = SchoolYear::query()->where('is_active', true)->value('id');

        return SchoolClass::query()
            ->when($yearId, fn ($query) => $query->where('school_year_id', $yearId))
            ->pluck('id')
            ->all();
    }

    /**
     * @return array{month: string|null, class_id: int|null, status: string|null}
     */
    private function filters(Request $request): array
    {
        return [
            'month' => $request->query('month'),
            'class_id' => $request->integer('class_id') ?: null,
            'status' => $request->query('status'),
        ];
    }
}
