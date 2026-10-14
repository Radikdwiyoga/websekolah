<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\BackupPeriod;
use App\Enums\PermitType;
use App\Models\AttendancePermit;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceBackupService
{
    /**
     * Build an attendance backup for the given period, classes and optional class filter.
     *
     * The recap counts attendance records (which already include approved izin/sakit
     * permits) and additionally exposes the raw permit requests so the export can
     * present izin & sakit as their own connected section.
     *
     * @param  array<int, int>  $classIds
     * @return array{
     *     period: BackupPeriod,
     *     periodLabel: string,
     *     start: Carbon,
     *     end: Carbon,
     *     rangeLabel: string,
     *     classes: Collection<int, SchoolClass>,
     *     selectedClassId: int|null,
     *     className: string,
     *     summary: array{siswa: int, hadir: int, terlambat: int, izin: int, sakit: int, alfa: int, total: int, permits: int, permits_izin: int, permits_sakit: int},
     *     rows: Collection<int, array{name: string, class: string, hadir: int, terlambat: int, izin: int, sakit: int, alfa: int, total: int, percentage: int|null}>,
     *     records: Collection<int, array{date: string, class: string, student: string, status: string, method: string, note: string}>,
     *     permits: Collection<int, array{date: string, class: string, student: string, type: string, reason: string, status: string, reviewer: string, reviewed_at: string, note: string}>
     * }
     */
    public function build(array $classIds, string $period, ?string $date, ?int $classId): array
    {
        $periodEnum = BackupPeriod::tryFrom($period) ?? BackupPeriod::Monthly;
        $anchor = $this->resolveAnchor($date);
        [$start, $end, $rangeLabel] = $this->resolveRange($periodEnum, $anchor);

        $classes = SchoolClass::query()
            ->whereIn('id', $classIds)
            ->orderBy('name')
            ->get();

        $scope = $this->resolveScope($classIds, $classId);
        $selectedClass = $classId !== null ? $classes->firstWhere('id', $classId) : null;

        $students = Student::query()
            ->whereIn('class_id', $scope)
            ->where('is_active', true)
            ->with(['user', 'schoolClass'])
            ->get()
            ->sortBy(fn (Student $student): string => ($student->schoolClass?->name ?? '').'|'.($student->user?->name ?? ''))
            ->values();

        $sessions = AttendanceSession::query()
            ->whereIn('class_id', $scope)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $records = AttendanceRecord::query()
            ->whereIn('attendance_session_id', $sessions->pluck('id'))
            ->with(['student.user', 'student.schoolClass', 'attendanceSession'])
            ->get();

        $recordsByStudent = $records->groupBy('student_id');

        $rows = $students
            ->map(fn (Student $student): array => $this->buildRow($student, $recordsByStudent->get($student->id, collect())))
            ->values();

        $permits = AttendancePermit::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereHas('student', fn ($query) => $query->whereIn('class_id', $scope))
            ->with(['student.user', 'student.schoolClass', 'reviewedBy'])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        return [
            'period' => $periodEnum,
            'periodLabel' => $periodEnum->label(),
            'start' => $start,
            'end' => $end,
            'rangeLabel' => $rangeLabel,
            'classes' => $classes,
            'selectedClassId' => $selectedClass?->id,
            'className' => $selectedClass?->name ?? 'Semua Kelas',
            'summary' => [
                'siswa' => $rows->count(),
                'hadir' => (int) $rows->sum('hadir'),
                'terlambat' => (int) $rows->sum('terlambat'),
                'izin' => (int) $rows->sum('izin'),
                'sakit' => (int) $rows->sum('sakit'),
                'alfa' => (int) $rows->sum('alfa'),
                'total' => (int) $rows->sum('total'),
                'permits' => $permits->count(),
                'permits_izin' => $permits->where('type', PermitType::Izin)->count(),
                'permits_sakit' => $permits->where('type', PermitType::Sakit)->count(),
            ],
            'rows' => $rows,
            'records' => $records
                ->sortBy(fn (AttendanceRecord $record): string => ($record->attendanceSession?->date?->toDateString() ?? '').'|'.($record->student?->schoolClass?->name ?? '').'|'.($record->student?->user?->name ?? ''))
                ->values()
                ->map(fn (AttendanceRecord $record): array => [
                    'date' => $record->attendanceSession?->date?->translatedFormat('d M Y') ?? '—',
                    'class' => $record->student?->schoolClass?->name ?? '—',
                    'student' => $record->student?->user?->name ?? '—',
                    'status' => $record->status?->label() ?? '—',
                    'method' => $record->method?->label() ?? '—',
                    'note' => $record->note ?? '',
                ]),
            'permits' => $permits->map(fn (AttendancePermit $permit): array => [
                'date' => $permit->date?->translatedFormat('d M Y') ?? '—',
                'class' => $permit->student?->schoolClass?->name ?? '—',
                'student' => $permit->student?->user?->name ?? '—',
                'type' => $permit->type?->label() ?? '—',
                'reason' => $permit->reason ?? '',
                'status' => $permit->status?->label() ?? '—',
                'reviewer' => $permit->reviewedBy?->name ?? '—',
                'reviewed_at' => $permit->reviewed_at?->translatedFormat('d M Y H.i') ?? '—',
                'note' => $permit->review_note ?? '',
            ]),
        ];
    }

    /**
     * @param  Collection<int, AttendanceRecord>  $records
     * @return array{name: string, class: string, hadir: int, terlambat: int, izin: int, sakit: int, alfa: int, total: int, percentage: int|null}
     */
    private function buildRow(Student $student, Collection $records): array
    {
        $hadir = $records->where('status', AttendanceStatus::Hadir)->count();
        $terlambat = $records->where('status', AttendanceStatus::Terlambat)->count();
        $izin = $records->where('status', AttendanceStatus::Izin)->count();
        $sakit = $records->where('status', AttendanceStatus::Sakit)->count();
        $alfa = $records->where('status', AttendanceStatus::Alfa)->count();

        $total = $hadir + $terlambat + $izin + $sakit + $alfa;
        $present = $hadir + $terlambat;

        return [
            'name' => $student->user?->name ?? '—',
            'class' => $student->schoolClass?->name ?? '—',
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'sakit' => $sakit,
            'alfa' => $alfa,
            'total' => $total,
            'percentage' => $total > 0 ? (int) round($present / $total * 100) : null,
        ];
    }

    private function resolveAnchor(?string $date): Carbon
    {
        if ($date === null || $date === '') {
            return Carbon::today();
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception) {
            return Carbon::today();
        }
    }

    /**
     * @param  array<int, int>  $classIds
     * @return array<int, int>
     */
    private function resolveScope(array $classIds, ?int $classId): array
    {
        if ($classId === null || ! in_array($classId, $classIds, true)) {
            return $classIds;
        }

        return [$classId];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveRange(BackupPeriod $period, Carbon $anchor): array
    {
        if ($period === BackupPeriod::Daily) {
            return [
                $anchor->copy()->startOfDay(),
                $anchor->copy()->endOfDay(),
                $anchor->translatedFormat('d F Y'),
            ];
        }

        if ($period === BackupPeriod::Weekly) {
            $weekStart = $anchor->copy()->startOfWeek();
            $weekEnd = $anchor->copy()->endOfWeek();

            return [
                $weekStart,
                $weekEnd,
                'Pekan '.$weekStart->translatedFormat('d M').' – '.$weekEnd->translatedFormat('d M Y'),
            ];
        }

        return [
            $anchor->copy()->startOfMonth(),
            $anchor->copy()->endOfMonth(),
            $anchor->translatedFormat('F Y'),
        ];
    }
}
