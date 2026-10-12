<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Http\Requests\Attendance\UpdateAttendanceRecordsRequest;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Services\AttendanceRecapService;
use App\Services\AttendanceService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use LogicException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherAttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendance,
        private readonly SettingService $settings,
        private readonly AttendanceRecapService $recaps,
    ) {}

    public function index(Request $request): View
    {
        $teacher = $request->user()?->teacher;

        return view('attendance.teacher.index', [
            'teacher' => $teacher,
            'classes' => $teacher ? $this->attendance->teacherClasses($teacher) : collect(),
            'openTime' => $this->settings->get('attendance_open_time', '07:00'),
            'closeTime' => $this->settings->get('attendance_close_time', '09:00'),
            'lateAfter' => $this->settings->get('attendance_late_after', '07:15'),
        ]);
    }

    public function recap(Request $request): View
    {
        return view('attendance.teacher.recap', [
            'recap' => $this->recaps->build($this->teacherClassIds($request), $this->filters($request)),
        ]);
    }

    public function recapExport(Request $request): StreamedResponse
    {
        return $this->recaps->download($this->recaps->build($this->teacherClassIds($request), $this->filters($request)));
    }

    public function show(SchoolClass $schoolClass): View
    {
        $this->authorize('manageClass', [AttendanceSession::class, $schoolClass]);

        $students = $schoolClass->students()
            ->with('user')
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($student) => $student->user?->name)
            ->values();

        $session = $this->attendance->todaySessionFor($schoolClass);
        $remainingMinutes = $this->remainingMinutes($session);

        return view('attendance.teacher.show', [
            'class' => $schoolClass,
            'session' => $session,
            'students' => $students,
            'recordsByStudent' => $session?->records->keyBy('student_id') ?? collect(),
            'statuses' => AttendanceStatus::cases(),
            'remainingMinutes' => $remainingMinutes,
            'settings' => $this->settings,
        ]);
    }

    public function open(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $this->authorize('manageClass', [AttendanceSession::class, $schoolClass]);

        try {
            $session = $this->attendance->openSession($schoolClass, $request->user());
        } catch (LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('teacher.attendance.show', $schoolClass)
            ->with('success', $session->wasRecentlyCreated
                ? 'Sesi absensi berhasil dibuka untuk '.$schoolClass->name.'.'
                : 'Sesi absensi untuk '.$schoolClass->name.' sudah berjalan.');
    }

    public function close(Request $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $this->authorize('close', $attendanceSession);

        try {
            $this->attendance->closeSession($attendanceSession, $request->user());
        } catch (LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Sesi absensi ditutup. Siswa yang belum absen ditandai alfa.');
    }

    public function update(AttendanceSession $attendanceSession, UpdateAttendanceRecordsRequest $request): RedirectResponse
    {
        $changed = $this->attendance->updateRecords(
            $attendanceSession,
            $request->user(),
            $request->validated('status', []),
            $request->validated('note', []),
        );

        return back()->with('success', $changed > 0
            ? 'Data kehadiran diperbarui untuk '.$changed.' siswa.'
            : 'Tidak ada perubahan yang perlu disimpan.');
    }

    /**
     * @return array<int, int>
     */
    private function teacherClassIds(Request $request): array
    {
        $teacher = $request->user()?->teacher;

        return $teacher ? $this->attendance->teacherClassIds($teacher) : [];
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

    private function remainingMinutes(?AttendanceSession $session): ?int
    {
        if (! $session?->isOpen()) {
            return null;
        }

        $closeAt = Carbon::createFromFormat('H:i', $session->close_at)->setDateFrom(now());

        return now()->lt($closeAt) ? max(0, (int) now()->diffInMinutes($closeAt)) : 0;
    }
}
