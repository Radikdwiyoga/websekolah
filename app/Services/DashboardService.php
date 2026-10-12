<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Enums\AttendanceStatus;
use App\Enums\NotificationStatus;
use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SystemLog;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Models\WhatsAppNotificationLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardService
{
    /**
     * Build the data needed by the student dashboard.
     *
     * @return array{
     *     student: Student|null,
     *     todayRecord: AttendanceRecord|null,
     *     tugasAktif: int,
     *     nilaiRata: int|null,
     *     tugas: Collection<int, array{assignment: Assignment, status: array{key: string, label: string, variant: string}}>,
     *     greeting: string,
     * }
     */
    public function student(User $user): array
    {
        $student = $user->student()->with('schoolClass')->first();
        $activeYear = $this->activeSchoolYear();

        $todayRecord = $student
            ? AttendanceRecord::query()
                ->where('student_id', $student->id)
                ->whereHas('attendanceSession', fn ($query) => $query->whereDate('date', now()->toDateString()))
                ->with(['attendanceSession.schoolClass'])
                ->latest('checked_in_at')
                ->first()
            : null;

        $assignments = $this->assignmentsForClass($student?->class_id, $activeYear?->id);

        return [
            'student' => $student,
            'todayRecord' => $todayRecord,
            'tugasAktif' => $assignments
                ->where(fn (Assignment $assignment) => $assignment->deadline === null || $assignment->deadline->isFuture())
                ->count(),
            'nilaiRata' => $student ? $this->averageGrade($student->id) : null,
            'tugas' => $this->tasksWithStatus($student, $assignments),
            'greeting' => $this->greeting(),
        ];
    }

    /**
     * Build the data needed by the teacher dashboard.
     *
     * @return array{
     *     teacher: Teacher|null,
     *     kpi: array{kelas: int, siswa: int, tugasAktif: int, perluDinilai: int},
     *     kelasDiampu: Collection<int, array{schoolClass: SchoolClass, subjects: Collection<int, string>, studentCount: int}>,
     *     sessionsToday: Collection<int, AttendanceSession>,
     *     ungraded: Collection<int, Assignment>,
     *     greeting: string,
     * }
     */
    public function teacher(User $user): array
    {
        $teacher = $user->teacher;
        $activeYear = $this->activeSchoolYear();

        if (! $teacher) {
            return [
                'teacher' => null,
                'kpi' => ['kelas' => 0, 'siswa' => 0, 'tugasAktif' => 0, 'perluDinilai' => 0],
                'kelasDiampu' => collect(),
                'sessionsToday' => collect(),
                'ungraded' => collect(),
                'greeting' => $this->greeting(),
            ];
        }

        $teachings = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->when($activeYear, fn ($query) => $query->where('school_year_id', $activeYear->id))
            ->with(['schoolClass', 'subject'])
            ->get();

        $classIds = $teachings->pluck('class_id')->unique()->values()->all();

        $classCounts = SchoolClass::query()
            ->whereIn('id', $classIds)
            ->withCount(['students' => fn ($query) => $query->where('is_active', true)])
            ->get()
            ->mapWithKeys(fn (SchoolClass $class) => [$class->id => $class->students_count]);

        $kelasDiampu = $teachings
            ->groupBy('class_id')
            ->map(function (Collection $group, int $classId) use ($classCounts): array {
                /** @var TeachingAssignment $first */
                $first = $group->first();

                return [
                    'schoolClass' => $first->schoolClass,
                    'subjects' => $group
                        ->map(fn (TeachingAssignment $teaching) => $teaching->subject?->name)
                        ->filter()
                        ->unique()
                        ->values(),
                    'studentCount' => $classCounts[$classId] ?? 0,
                ];
            })
            ->values();

        $assignmentIds = Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->when($activeYear, fn ($query) => $query->where('school_year_id', $activeYear->id))
            ->pluck('id');

        $perluDinilai = AssignmentSubmission::query()
            ->whereIn('assignment_id', $assignmentIds)
            ->where('status', SubmissionStatus::Submitted)
            ->count();

        $tugasAktif = Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', AssignmentStatus::Published)
            ->when($activeYear, fn ($query) => $query->where('school_year_id', $activeYear->id))
            ->where(function ($query) {
                $query->whereNull('deadline')
                    ->orWhere('deadline', '>=', now());
            })
            ->count();

        $sessionsToday = AttendanceSession::query()
            ->whereDate('date', now()->toDateString())
            ->whereIn('class_id', $classIds)
            ->with('schoolClass')
            ->orderBy('open_at')
            ->get();

        $ungraded = Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->when($activeYear, fn ($query) => $query->where('school_year_id', $activeYear->id))
            ->whereHas('submissions', fn ($query) => $query->where('status', SubmissionStatus::Submitted))
            ->with(['schoolClass', 'subject'])
            ->withCount(['submissions as submitted_count' => fn ($query) => $query->where('status', SubmissionStatus::Submitted)])
            ->latest()
            ->limit(5)
            ->get();

        return [
            'teacher' => $teacher,
            'kpi' => [
                'kelas' => count($classIds),
                'siswa' => Student::query()->whereIn('class_id', $classIds)->where('is_active', true)->count(),
                'tugasAktif' => $tugasAktif,
                'perluDinilai' => $perluDinilai,
            ],
            'kelasDiampu' => $kelasDiampu,
            'sessionsToday' => $sessionsToday,
            'ungraded' => $ungraded,
            'greeting' => $this->greeting(),
        ];
    }

    /**
     * Build the data needed by the parent dashboard.
     *
     * Only children linked to the guardian are ever considered, so passing an
     * arbitrary student id silently falls back to the guardian's first child.
     *
     * @return array{
     *     children: Collection<int, Student>,
     *     selectedChild: Student|null,
     *     selectedChildId: int|null,
     *     kpi: array{hadir: int, terlambat: int, tidakHadir: int, nilaiRata: int|null},
     *     riwayat: Collection<int, AttendanceRecord>,
     *     tugas: Collection<int, array{assignment: Assignment, status: array{key: string, label: string, variant: string}}>,
     *     greeting: string,
     * }
     */
    public function parent(User $user, ?int $selectedChildId): array
    {
        $context = $this->resolveParentChild($user, $selectedChildId);
        $child = $context['selectedChild'];

        if ($child === null) {
            return $context + [
                'kpi' => $this->attendanceSummary(collect(), null),
                'riwayat' => collect(),
                'tugas' => collect(),
                'greeting' => $this->greeting(),
            ];
        }

        $records = $this->attendanceRecords($child);
        $activeYear = $this->activeSchoolYear();

        return $context + [
            'kpi' => $this->attendanceSummary($records, $child->id),
            'riwayat' => $records->take(5)->values(),
            'tugas' => $this->tasksWithStatus($child, $this->assignmentsForClass($child->class_id, $activeYear?->id)),
            'greeting' => $this->greeting(),
        ];
    }

    /**
     * Build the data needed by the parent's "Kehadiran Anak" page.
     *
     * @return array{children: Collection<int, Student>, selectedChild: Student|null, selectedChildId: int|null, kpi: array{hadir: int, terlambat: int, tidakHadir: int, nilaiRata: int|null}, records: Collection<int, AttendanceRecord>, greeting: string}
     */
    public function parentAttendance(User $user, ?int $selectedChildId): array
    {
        $context = $this->resolveParentChild($user, $selectedChildId);
        $child = $context['selectedChild'];

        if ($child === null) {
            return $context + [
                'kpi' => $this->attendanceSummary(collect(), null),
                'records' => collect(),
                'greeting' => $this->greeting(),
            ];
        }

        $records = $this->attendanceRecords($child);

        return $context + [
            'kpi' => $this->attendanceSummary($records, $child->id),
            'records' => $records,
            'greeting' => $this->greeting(),
        ];
    }

    /**
     * Build the data needed by the parent's "Tugas Anak" page.
     *
     * @return array{children: Collection<int, Student>, selectedChild: Student|null, selectedChildId: int|null, tasks: Collection<int, array{assignment: Assignment, status: array{key: string, label: string, variant: string}, score: float|null}>, greeting: string}
     */
    public function parentAssignments(User $user, ?int $selectedChildId): array
    {
        $context = $this->resolveParentChild($user, $selectedChildId);
        $child = $context['selectedChild'];

        if ($child === null) {
            return $context + [
                'tasks' => collect(),
                'greeting' => $this->greeting(),
            ];
        }

        $activeYear = $this->activeSchoolYear();

        $assignments = Assignment::query()
            ->where('class_id', $child->class_id)
            ->when($activeYear, fn ($query, $year) => $query->where('school_year_id', $year->id))
            ->where('status', AssignmentStatus::Published)
            ->with(['subject', 'submissions' => fn ($query) => $query->where('student_id', $child->id)])
            ->latest()
            ->get();

        return $context + [
            'tasks' => $assignments->map(function (Assignment $assignment): array {
                $submission = $assignment->submissions->first();

                return [
                    'assignment' => $assignment,
                    'status' => $this->taskStatus($assignment, $submission),
                    'score' => $this->submissionScore($submission),
                ];
            }),
            'greeting' => $this->greeting(),
        ];
    }

    /**
     * Build the data needed by the parent's "Nilai Anak" page.
     *
     * @return array{children: Collection<int, Student>, selectedChild: Student|null, selectedChildId: int|null, grades: Collection<int, Grade>, average: int|null, greeting: string}
     */
    public function parentGrades(User $user, ?int $selectedChildId): array
    {
        $context = $this->resolveParentChild($user, $selectedChildId);
        $child = $context['selectedChild'];

        if ($child === null) {
            return $context + [
                'grades' => collect(),
                'average' => null,
                'greeting' => $this->greeting(),
            ];
        }

        $grades = Grade::query()
            ->where('student_id', $child->id)
            ->with(['subject', 'assignment'])
            ->latest('graded_at')
            ->get();

        return $context + [
            'grades' => $grades,
            'average' => $this->averageGrade($child->id),
            'greeting' => $this->greeting(),
        ];
    }

    /**
     * Build the data needed by the student's "Nilai" page.
     *
     * @return array{
     *     student: Student|null,
     *     grades: Collection<int, Grade>,
     *     average: int|null,
     *     highest: float|null,
     *     bySubject: Collection<int, array{subject: Subject|null, average: int, count: int}>,
     * }
     */
    public function studentGrades(User $user): array
    {
        $student = $user->student()->with('schoolClass')->first();

        if ($student === null) {
            return [
                'student' => null,
                'grades' => collect(),
                'average' => null,
                'highest' => null,
                'bySubject' => collect(),
            ];
        }

        $grades = Grade::query()
            ->where('student_id', $student->id)
            ->with(['subject', 'assignment'])
            ->latest('graded_at')
            ->get();

        $bySubject = $grades
            ->groupBy(fn (Grade $grade): string => (string) ($grade->subject_id ?? 0))
            ->map(function (Collection $subjectGrades): array {
                /** @var Grade $first */
                $first = $subjectGrades->first();

                return [
                    'subject' => $first->subject,
                    'average' => (int) round((float) $subjectGrades->avg('score')),
                    'count' => $subjectGrades->count(),
                ];
            })
            ->values();

        return [
            'student' => $student,
            'grades' => $grades,
            'average' => $this->averageGrade($student->id),
            'highest' => $grades->isEmpty() ? null : (float) $grades->max('score'),
            'bySubject' => $bySubject,
        ];
    }

    /**
     * Resolve the guardian's linked children and the currently selected child.
     *
     * A child id that is not linked to the guardian is ignored and falls back
     * to the first child, so a parent can never reach another family's data by
     * guessing an id.
     *
     * @return array{children: Collection<int, Student>, selectedChild: Student|null, selectedChildId: int|null}
     */
    private function resolveParentChild(User $user, ?int $childId): array
    {
        $guardian = $user->guardian()->with(['students.user', 'students.schoolClass'])->first();
        $children = ($guardian?->students ?? collect())->values();
        $selectedChild = $children->firstWhere('id', $childId) ?? $children->first();

        return [
            'children' => $children,
            'selectedChild' => $selectedChild,
            'selectedChildId' => $selectedChild?->id,
        ];
    }

    /**
     * Every attendance record for a student, newest session first.
     *
     * @return Collection<int, AttendanceRecord>
     */
    private function attendanceRecords(Student $student): Collection
    {
        return AttendanceRecord::query()
            ->where('student_id', $student->id)
            ->with('attendanceSession.schoolClass')
            ->get()
            ->sortByDesc(fn (AttendanceRecord $record): int => optional($record->attendanceSession?->date)->getTimestamp() ?? 0)
            ->values();
    }

    /**
     * @param  Collection<int, AttendanceRecord>  $records
     * @return array{hadir: int, terlambat: int, tidakHadir: int, nilaiRata: int|null}
     */
    private function attendanceSummary(Collection $records, ?int $studentId): array
    {
        return [
            'hadir' => $records->where('status', AttendanceStatus::Hadir)->count(),
            'terlambat' => $records->where('status', AttendanceStatus::Terlambat)->count(),
            'tidakHadir' => $records
                ->where(fn (AttendanceRecord $record): bool => in_array(
                    $record->status,
                    [AttendanceStatus::Izin, AttendanceStatus::Sakit, AttendanceStatus::Alfa],
                    true,
                ))
                ->count(),
            'nilaiRata' => $studentId === null ? null : $this->averageGrade($studentId),
        ];
    }

    /**
     * Build the data needed by the admin dashboard.
     *
     * @return array{
     *     kpi: array{siswa: int, guru: int, kehadiranHariIni: int, notifWa: int, tugasAktif: int},
     *     attendanceToday: Collection<int, array{schoolClass: SchoolClass, session: AttendanceSession|null, studentCount: int, hadir: int, terlambat: int, tidakHadir: int, belum: int}>,
     *     gradingQueue: array{count: int, assignments: Collection<int, Assignment>},
     *     notifications: Collection<int, WhatsAppNotificationLog>,
     *     activity: Collection<int, SystemLog>,
     *     quickActions: array<int, array{icon: string, title: string, description: string, route: string|null}>,
     *     health: array<int, array{label: string, value: string, variant: string}>,
     *     greeting: string,
     * }
     */
    public function admin(): array
    {
        try {
            DB::connection()->getPdo();
            $database = ['value' => 'Normal', 'variant' => 'success'];
        } catch (Throwable) {
            $database = ['value' => 'Bermasalah', 'variant' => 'danger'];
        }

        $latestNotification = WhatsAppNotificationLog::query()->latest('created_at')->first();

        $whatsapp = match ($latestNotification?->status) {
            NotificationStatus::Sent => ['value' => 'Normal', 'variant' => 'success'],
            NotificationStatus::Failed => ['value' => 'Perlu Perhatian', 'variant' => 'warning'],
            NotificationStatus::Pending => ['value' => 'Menunggu', 'variant' => 'info'],
            default => ['value' => 'Belum Digunakan', 'variant' => 'neutral'],
        };

        return [
            'kpi' => [
                'siswa' => Student::query()->count(),
                'guru' => Teacher::query()->count(),
                'kehadiranHariIni' => AttendanceRecord::query()
                    ->whereHas('attendanceSession', fn ($query) => $query->whereDate('date', now()->toDateString()))
                    ->whereIn('status', [AttendanceStatus::Hadir, AttendanceStatus::Terlambat])
                    ->count(),
                'notifWa' => WhatsAppNotificationLog::query()->whereDate('created_at', now()->toDateString())->count(),
                'tugasAktif' => Assignment::query()
                    ->where('status', AssignmentStatus::Published)
                    ->where(function ($query) {
                        $query->whereNull('deadline')
                            ->orWhere('deadline', '>=', now());
                    })
                    ->count(),
            ],
            'attendanceToday' => $this->attendanceToday($this->activeSchoolYear()),
            'gradingQueue' => [
                'count' => $this->pendingSubmissions(),
                'assignments' => $this->gradingQueue(),
            ],
            'notifications' => WhatsAppNotificationLog::query()->latest()->limit(5)->get(),
            'activity' => SystemLog::query()->with('user')->latest()->limit(5)->get(),
            'quickActions' => [
                ['icon' => 'students', 'title' => 'Tambah Siswa', 'description' => 'Daftarkan siswa baru ke sekolah.', 'route' => 'admin.students.index'],
                ['icon' => 'teacher', 'title' => 'Tambah Guru', 'description' => 'Daftarkan guru baru beserta akun.', 'route' => 'admin.teachers.index'],
                ['icon' => 'announcement', 'title' => 'Buat Pengumuman', 'description' => 'Terbitkan pengumuman untuk seluruh warga sekolah.', 'route' => 'admin.announcements.create'],
                ['icon' => 'download', 'title' => 'Import Excel', 'description' => 'Impor data siswa & guru dari berkas Excel.', 'route' => 'admin.settings.index'],
            ],
            'health' => [
                ['label' => 'Website', 'value' => 'Online', 'variant' => 'success'],
                ['label' => 'Database', 'value' => $database['value'], 'variant' => $database['variant']],
                ['label' => 'WhatsApp', 'value' => $whatsapp['value'], 'variant' => $whatsapp['variant']],
                ['label' => 'Backup', 'value' => 'Belum Diatur', 'variant' => 'neutral'],
                ['label' => 'Penyimpanan', 'value' => '—', 'variant' => 'neutral'],
            ],
            'greeting' => $this->greeting(),
        ];
    }

    /**
     * Today's attendance progress for every class in the active school year.
     *
     * @return Collection<int, array{schoolClass: SchoolClass, session: AttendanceSession|null, studentCount: int, hadir: int, terlambat: int, tidakHadir: int, belum: int}>
     */
    private function attendanceToday(?SchoolYear $schoolYear): Collection
    {
        $classes = SchoolClass::query()
            ->when($schoolYear, fn ($query) => $query->where('school_year_id', $schoolYear->id))
            ->withCount(['students' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        if ($classes->isEmpty()) {
            return collect();
        }

        $sessions = AttendanceSession::query()
            ->whereDate('date', now()->toDateString())
            ->whereIn('class_id', $classes->pluck('id'))
            ->with('records')
            ->get()
            ->keyBy('class_id');

        return $classes->map(function (SchoolClass $class) use ($sessions): array {
            $session = $sessions->get($class->id);
            $records = $session?->records ?? collect();

            return [
                'schoolClass' => $class,
                'session' => $session,
                'studentCount' => $class->students_count,
                'hadir' => $records->where('status', AttendanceStatus::Hadir)->count(),
                'terlambat' => $records->where('status', AttendanceStatus::Terlambat)->count(),
                'tidakHadir' => $records
                    ->where(fn (AttendanceRecord $record): bool => in_array(
                        $record->status,
                        [AttendanceStatus::Izin, AttendanceStatus::Sakit, AttendanceStatus::Alfa],
                        true,
                    ))
                    ->count(),
                'belum' => $records->where('status', AttendanceStatus::BelumAbsen)->count(),
            ];
        });
    }

    /**
     * Total submissions across the school still waiting to be graded.
     */
    private function pendingSubmissions(): int
    {
        return AssignmentSubmission::query()
            ->where('status', SubmissionStatus::Submitted)
            ->count();
    }

    /**
     * Assignments that still have submissions waiting to be graded.
     *
     * @return Collection<int, Assignment>
     */
    private function gradingQueue(): Collection
    {
        return Assignment::query()
            ->whereHas('submissions', fn ($query) => $query->where('status', SubmissionStatus::Submitted))
            ->with(['schoolClass', 'subject', 'teacher.user'])
            ->withCount(['submissions as pending_count' => fn ($query) => $query->where('status', SubmissionStatus::Submitted)])
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * The currently active school year, falling back to the most recent one.
     */
    private function activeSchoolYear(): ?SchoolYear
    {
        return SchoolYear::query()->where('is_active', true)->first()
            ?? SchoolYear::query()->latest('id')->first();
    }

    /**
     * Published assignments for a class in a school year, newest first.
     *
     * @return Collection<int, Assignment>
     */
    private function assignmentsForClass(?int $classId, ?int $schoolYearId): Collection
    {
        if ($classId === null) {
            return collect();
        }

        return Assignment::query()
            ->where('class_id', $classId)
            ->when($schoolYearId, fn ($query) => $query->where('school_year_id', $schoolYearId))
            ->where('status', AssignmentStatus::Published)
            ->with('subject')
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Attach the student's task status to a list of assignments.
     *
     * @param  Collection<int, Assignment>  $assignments
     * @return Collection<int, array{assignment: Assignment, status: array{key: string, label: string, variant: string}}>
     */
    private function tasksWithStatus(?Student $student, Collection $assignments): Collection
    {
        if ($student === null) {
            return $assignments->map(fn (Assignment $assignment) => [
                'assignment' => $assignment,
                'status' => $this->taskStatus($assignment, null),
            ]);
        }

        $submissions = AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        return $assignments->map(fn (Assignment $assignment) => [
            'assignment' => $assignment,
            'status' => $this->taskStatus($assignment, $submissions->get($assignment->id)),
        ]);
    }

    /**
     * Resolve the badge data describing a student's task progress.
     *
     * @return array{key: string, label: string, variant: string}
     */
    private function taskStatus(Assignment $assignment, ?AssignmentSubmission $submission): array
    {
        if ($submission && in_array($submission->status, [SubmissionStatus::Submitted, SubmissionStatus::Graded], true)) {
            return ['key' => 'selesai', 'label' => 'Selesai', 'variant' => 'success'];
        }

        if ($submission && $submission->status === SubmissionStatus::Draft) {
            return ['key' => 'dikerjakan', 'label' => 'Dalam Pengerjaan', 'variant' => 'info'];
        }

        if ($assignment->isOverdue()) {
            return ['key' => 'terlambat', 'label' => 'Terlambat', 'variant' => 'danger'];
        }

        return ['key' => 'belum', 'label' => 'Belum Dikerjakan', 'variant' => 'neutral'];
    }

    private function averageGrade(int $studentId): ?int
    {
        $average = Grade::query()->where('student_id', $studentId)->avg('score');

        return $average === null ? null : (int) round($average);
    }

    /**
     * The score shown to parents: the teacher override when present, otherwise
     * the automatically computed total.
     */
    private function submissionScore(?AssignmentSubmission $submission): ?float
    {
        $score = $submission?->teacher_score ?? $submission?->total_score;

        return $score === null ? null : round((float) $score, 2);
    }

    /**
     * A time-of-day greeting in Indonesian.
     */
    public function greeting(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour >= 5 && $hour < 11 => 'Selamat pagi',
            $hour >= 11 && $hour < 15 => 'Selamat siang',
            $hour >= 15 && $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }
}
