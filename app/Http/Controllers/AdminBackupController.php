<?php

namespace App\Http\Controllers;

use App\Enums\BackupPeriod;
use App\Enums\SystemLogStatus;
use App\Exports\AssignmentGradeBackupExport;
use App\Exports\AttendanceBackupExport;
use App\Http\Requests\Backup\AssignmentBackupRequest;
use App\Http\Requests\Backup\AttendanceBackupRequest;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\SystemLog;
use App\Services\AssignmentGradeBackupService;
use App\Services\AttendanceBackupService;
use App\Services\SchoolProfileService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class AdminBackupController extends Controller
{
    public function __construct(
        private readonly AttendanceBackupService $attendanceBackups,
        private readonly AssignmentGradeBackupService $assignmentBackups,
        private readonly SchoolProfileService $school,
    ) {}

    /**
     * Display the backup & export hub with the export forms.
     */
    public function index(): View
    {
        return view('admin.backups.index', [
            'classes' => $this->classes(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'periods' => BackupPeriod::cases(),
            'defaultDate' => now()->toDateString(),
        ]);
    }

    /**
     * Download the attendance backup (daily, weekly or monthly) as Excel or PDF.
     */
    public function attendance(AttendanceBackupRequest $request): Response
    {
        $backup = $this->attendanceBackups->build(
            $this->classIds(),
            $request->period()->value,
            $request->input('date'),
            $request->classId(),
        );

        $this->log('backup_attendance', 'Mengunduh backup absensi '.$backup['periodLabel'].' ('.$backup['rangeLabel'].') dalam format '.strtoupper($request->exportFormat()));

        $filename = 'backup-absensi-'.$backup['period']->value.'-'.$backup['start']->format('Ymd');

        if ($request->exportFormat() === 'pdf') {
            return Pdf::loadView('exports.attendance', [
                'backup' => $backup,
                'school' => $this->school->all(),
                'generatedAt' => now(),
            ])->setPaper('a4', 'landscape')->download($filename.'.pdf');
        }

        return Excel::download(new AttendanceBackupExport($backup), $filename.'.xlsx');
    }

    /**
     * Download the assignment & grade summary backup as Excel or PDF.
     */
    public function assignments(AssignmentBackupRequest $request): Response
    {
        $backup = $this->assignmentBackups->build(
            $this->classIds(),
            $request->classId(),
            $request->subjectId(),
            $request->input('start_date'),
            $request->input('end_date'),
        );

        $this->log('backup_assignments', 'Mengunduh backup tugas & penilaian ('.$backup['rangeLabel'].') dalam format '.strtoupper($request->exportFormat()));

        $filename = 'backup-tugas-nilai-'.now()->format('Ymd');

        if ($request->exportFormat() === 'pdf') {
            return Pdf::loadView('exports.assignment-grade', [
                'backup' => $backup,
                'school' => $this->school->all(),
                'generatedAt' => now(),
            ])->setPaper('a4', 'landscape')->download($filename.'.pdf');
        }

        return Excel::download(new AssignmentGradeBackupExport($backup), $filename.'.xlsx');
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    private function classes(): Collection
    {
        $yearId = SchoolYear::query()->where('is_active', true)->value('id');

        return SchoolClass::query()
            ->when($yearId, fn ($query) => $query->where('school_year_id', $yearId))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, int>
     */
    private function classIds(): array
    {
        return $this->classes()->pluck('id')->all();
    }

    private function log(string $action, string $description): void
    {
        SystemLog::query()->create([
            'user_id' => request()->user()?->id,
            'module' => 'backup',
            'action' => $action,
            'description' => $description,
            'status' => SystemLogStatus::Success,
        ]);
    }
}
