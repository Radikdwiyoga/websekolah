<?php

namespace App\Services;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceSessionStatus;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\AttendanceStatusChange;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

class AttendanceService
{
    public function __construct(private readonly SettingService $settings) {}

    public function activeSchoolYear(): ?SchoolYear
    {
        return SchoolYear::query()->where('is_active', true)->first();
    }

    /**
     * Classes the teacher may record attendance for: classes they teach in the
     * active school year plus classes where they are the homeroom teacher.
     *
     * @return array<int, int>
     */
    public function teacherClassIds(Teacher $teacher): array
    {
        $year = $this->activeSchoolYear();

        $taClassIds = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->when($year, fn ($query) => $query->where('school_year_id', $year->id))
            ->pluck('class_id');

        $homeroomClassIds = SchoolClass::query()
            ->where('homeroom_teacher_id', $teacher->id)
            ->pluck('id');

        return $taClassIds->merge($homeroomClassIds)->unique()->values()->all();
    }

    /**
     * Taught classes enriched with today's attendance session, if any.
     *
     * @return Collection<int, array{class: SchoolClass, session: AttendanceSession|null, studentCount: int, subjects: Collection<int, string>}>
     */
    public function teacherClasses(Teacher $teacher): Collection
    {
        $ids = $this->teacherClassIds($teacher);

        if ($ids === []) {
            return collect();
        }

        $year = $this->activeSchoolYear();

        $classes = SchoolClass::query()
            ->with([
                'teachingAssignments' => fn ($query) => $query
                    ->when($year, fn ($yearQuery) => $yearQuery->where('school_year_id', $year->id))
                    ->with('subject'),
            ])
            ->withCount(['students' => fn ($query) => $query->where('is_active', true)])
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();

        $sessionsToday = AttendanceSession::query()
            ->whereDate('date', now()->toDateString())
            ->whereIn('class_id', $ids)
            ->get()
            ->keyBy('class_id');

        return $classes
            ->map(fn (SchoolClass $class): array => [
                'class' => $class,
                'session' => $sessionsToday->get($class->id),
                'studentCount' => $class->students_count,
                'subjects' => $class->teachingAssignments
                    ->map(fn (TeachingAssignment $teaching) => $teaching->subject?->name)
                    ->filter()
                    ->unique()
                    ->values(),
            ])
            ->values();
    }

    public function todaySessionFor(SchoolClass $class): ?AttendanceSession
    {
        return AttendanceSession::query()
            ->where('class_id', $class->id)
            ->whereDate('date', now()->toDateString())
            ->with('records')
            ->first();
    }

    public function openSession(SchoolClass $class, User $openedBy): AttendanceSession
    {
        $existing = $this->todaySessionFor($class);

        if ($existing) {
            if ($existing->isOpen()) {
                return $existing;
            }

            throw new LogicException('Sesi absensi untuk '.$class->name.' sudah ditutup hari ini. Gunakan daftar kehadiran untuk memperbaiki data.');
        }

        $year = $this->activeSchoolYear();

        if (! $year) {
            throw new LogicException('Belum ada tahun ajaran aktif. Hubungi administrator.');
        }

        return AttendanceSession::create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => now()->toDateString(),
            'open_at' => $this->settings->get('attendance_open_time', '07:00'),
            'close_at' => $this->settings->get('attendance_close_time', '09:00'),
            'late_after' => $this->settings->get('attendance_late_after', '07:15'),
            'status' => AttendanceSessionStatus::Open,
            'opened_by' => $openedBy->id,
        ]);
    }

    public function closeSession(AttendanceSession $session, User $closedBy): void
    {
        if (! $session->isOpen()) {
            throw new LogicException('Sesi absensi ini sudah ditutup.');
        }

        DB::transaction(function () use ($session, $closedBy): void {
            $pending = $session->records()
                ->where('status', AttendanceStatus::BelumAbsen)
                ->get();

            foreach ($pending as $record) {
                $record->update([
                    'status' => AttendanceStatus::Alfa,
                    'updated_by' => $closedBy->id,
                ]);

                $this->logChange($record, AttendanceStatus::BelumAbsen, AttendanceStatus::Alfa, 'Sesi ditutup', $closedBy);
            }

            $session->update([
                'status' => AttendanceSessionStatus::Closed,
                'closed_by' => $closedBy->id,
                'closed_at' => now(),
            ]);
        });
    }

    /**
     * Apply the teacher's status edits for a session. Creates missing records
     * and logs a status change whenever an existing status is revised.
     *
     * @param  array<string, string>  $statuses  status per student id
     * @param  array<string, string>  $notes  optional note per student id
     */
    public function updateRecords(AttendanceSession $session, User $updatedBy, array $statuses, array $notes = []): int
    {
        $studentIds = $session->schoolClass
            ->students()
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id)
            ->all();

        $existing = $session->records()->get()->keyBy(fn (AttendanceRecord $record): string => (string) $record->student_id);

        $changed = 0;

        DB::transaction(function () use ($session, $updatedBy, $statuses, $notes, $studentIds, $existing, &$changed): void {
            foreach ($statuses as $studentId => $statusValue) {
                if (! in_array((string) $studentId, $studentIds, true)) {
                    continue;
                }

                $newStatus = AttendanceStatus::from($statusValue);
                $note = isset($notes[$studentId]) ? trim((string) $notes[$studentId]) : '';
                $record = $existing->get((string) $studentId);

                if ($record) {
                    if ($record->status === $newStatus && $record->note === ($note !== '' ? $note : null)) {
                        continue;
                    }

                    $previousStatus = $record->status;

                    $record->update([
                        'status' => $newStatus,
                        'note' => $note !== '' ? $note : null,
                        'updated_by' => $updatedBy->id,
                    ]);

                    if ($previousStatus !== $newStatus) {
                        $this->logChange($record, $previousStatus, $newStatus, $note !== '' ? $note : null, $updatedBy);
                    }

                    $changed++;

                    continue;
                }

                $session->records()->create([
                    'student_id' => $studentId,
                    'status' => $newStatus,
                    'checked_in_at' => $newStatus->isPresent() ? now() : null,
                    'method' => AttendanceMethod::Manual,
                    'note' => $note !== '' ? $note : null,
                    'updated_by' => $updatedBy->id,
                ]);

                $changed++;
            }
        });

        return $changed;
    }

    public function checkIn(AttendanceSession $session, Student $student, User $user): AttendanceRecord
    {
        if (! $session->isOpen()) {
            throw new LogicException('Sesi absensi sudah ditutup.');
        }

        if ($session->class_id !== $student->class_id) {
            throw new LogicException('Sesi absensi ini bukan untuk kelas Anda.');
        }

        if ($session->date?->toDateString() !== now()->toDateString()) {
            throw new LogicException('Sesi absensi ini bukan untuk hari ini.');
        }

        if ($session->records()->where('student_id', $student->id)->exists()) {
            throw new LogicException('Anda sudah melakukan absensi pada sesi ini.');
        }

        $lateAfter = $session->late_after ?? $this->settings->get('attendance_late_after', '07:15');
        $status = now()->format('H:i') >= $lateAfter ? AttendanceStatus::Terlambat : AttendanceStatus::Hadir;

        return $session->records()->create([
            'student_id' => $student->id,
            'status' => $status,
            'checked_in_at' => now(),
            'method' => AttendanceMethod::Button,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * Record a check-in triggered by scanning a student's permanent QR code.
     * The caller must have validated the QR payload signature first.
     */
    public function checkInQr(AttendanceSession $session, Student $student, User $user): AttendanceRecord
    {
        if (! $session->isOpen()) {
            throw new LogicException('Sesi absensi sudah ditutup.');
        }

        if ($session->class_id !== $student->class_id) {
            throw new LogicException('QR code ini milik siswa kelas lain.');
        }

        if ($session->date?->toDateString() !== now()->toDateString()) {
            throw new LogicException('Sesi absensi ini bukan untuk hari ini.');
        }

        if ($session->records()->where('student_id', $student->id)->exists()) {
            throw new LogicException('Siswa sudah melakukan absensi pada sesi ini.');
        }

        $lateAfter = $session->late_after ?? $this->settings->get('attendance_late_after', '07:15');
        $status = now()->format('H:i') >= $lateAfter ? AttendanceStatus::Terlambat : AttendanceStatus::Hadir;

        return $session->records()->create([
            'student_id' => $student->id,
            'status' => $status,
            'checked_in_at' => now(),
            'method' => AttendanceMethod::Qr,
            'updated_by' => $user->id,
            'note' => 'Absensi via QR code',
        ]);
    }

    private function logChange(AttendanceRecord $record, AttendanceStatus $from, AttendanceStatus $to, ?string $reason, User $changedBy): void
    {
        AttendanceStatusChange::create([
            'attendance_record_id' => $record->id,
            'from_status' => $from,
            'to_status' => $to,
            'reason' => $reason,
            'changed_by' => $changedBy->id,
        ]);
    }
}
