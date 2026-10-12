<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AttendanceRecapService
{
    /**
     * Build a monthly attendance recap for the classes the caller may see.
     *
     * A class id outside the allowed scope is ignored so a teacher can never
     * peek at another teacher's class by tampering with the filter.
     *
     * @param  Collection<int, int>|array<int, int>  $classIds
     * @param  array{month?: string|null, class_id?: int|null, status?: string|null}  $filters
     * @return array{
     *     classes: Collection<int, SchoolClass>,
     *     selectedClassId: int|null,
     *     month: string,
     *     monthLabel: string,
     *     status: AttendanceStatus|null,
     *     statuses: array<int, AttendanceStatus>,
     *     summary: array{siswa: int, hadir: int, terlambat: int, izin: int, sakit: int, alfa: int, total: int},
     *     rows: Collection<int, array{student: Student, hadir: int, terlambat: int, izin: int, sakit: int, alfa: int, total: int, percentage: int|null}>,
     * }
     */
    public function build(Collection|array $classIds, array $filters = []): array
    {
        $allowed = collect($classIds)->map(fn (int|string $id): int => (int) $id)->unique()->values();

        $classes = SchoolClass::query()
            ->whereIn('id', $allowed->all())
            ->orderBy('name')
            ->get();

        $requestedClassId = isset($filters['class_id']) ? (int) $filters['class_id'] : null;
        $selectedClassId = $requestedClassId !== null && $allowed->contains($requestedClassId) ? $requestedClassId : null;
        $scopeClassIds = $selectedClassId !== null ? [$selectedClassId] : $allowed->all();

        $month = $this->resolveMonth($filters['month'] ?? null);
        $status = $this->resolveStatus($filters['status'] ?? null);

        $students = Student::query()
            ->whereIn('class_id', $scopeClassIds)
            ->where('is_active', true)
            ->with(['user', 'schoolClass'])
            ->get()
            ->sortBy(fn (Student $student): string => ($student->schoolClass?->name ?? '').'|'.($student->user?->name ?? ''))
            ->values();

        $recordsByStudent = AttendanceRecord::query()
            ->whereIn('student_id', $students->pluck('id'))
            ->whereHas('attendanceSession', fn ($query) => $query
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month))
            ->get()
            ->groupBy('student_id');

        $rows = $students
            ->map(fn (Student $student): array => $this->buildRow($student, $recordsByStudent->get($student->id, collect())))
            ->when($status, fn (Collection $rows): Collection => $rows->filter(
                fn (array $row): bool => $row[$status->value] > 0,
            ))
            ->values();

        return [
            'classes' => $classes,
            'selectedClassId' => $selectedClassId,
            'month' => $month->format('Y-m'),
            'monthLabel' => $month->translatedFormat('F Y'),
            'status' => $status,
            'statuses' => $this->statusOptions(),
            'summary' => [
                'siswa' => $rows->count(),
                'hadir' => $rows->sum('hadir'),
                'terlambat' => $rows->sum('terlambat'),
                'izin' => $rows->sum('izin'),
                'sakit' => $rows->sum('sakit'),
                'alfa' => $rows->sum('alfa'),
                'total' => $rows->sum('total'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * Stream the recap as a CSV file that opens cleanly in Excel.
     *
     * @param  array{month: string, rows: Collection<int, array{student: Student, hadir: int, terlambat: int, izin: int, sakit: int, alfa: int, total: int, percentage: int|null}>}  $recap
     */
    public function download(array $recap): StreamedResponse
    {
        $filename = 'rekap-absensi-'.$recap['month'].'.csv';

        return response()->streamDownload(function () use ($recap): void {
            $handle = fopen('php://output', 'w');

            // BOM so Excel reads the Indonesian characters correctly.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['No', 'Nama', 'Kelas', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alfa', 'Total', 'Persentase (%)']);

            foreach ($recap['rows'] as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row['student']->user?->name ?? '—',
                    $row['student']->schoolClass?->name ?? '—',
                    $row['hadir'],
                    $row['terlambat'],
                    $row['izin'],
                    $row['sakit'],
                    $row['alfa'],
                    $row['total'],
                    $row['percentage'] ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  Collection<int, AttendanceRecord>  $records
     * @return array{student: Student, hadir: int, terlambat: int, izin: int, sakit: int, alfa: int, total: int, percentage: int|null}
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
            'student' => $student,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'sakit' => $sakit,
            'alfa' => $alfa,
            'total' => $total,
            'percentage' => $total > 0 ? (int) round(($present / $total) * 100) : null,
        ];
    }

    private function resolveMonth(?string $value): Carbon
    {
        if (is_string($value) && $value !== '') {
            try {
                return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
            } catch (Throwable) {
                // Fall back to the current month on malformed input.
            }
        }

        return now()->startOfMonth();
    }

    private function resolveStatus(?string $value): ?AttendanceStatus
    {
        $status = $value ? AttendanceStatus::tryFrom($value) : null;

        return $status !== null && in_array($status, $this->statusOptions(), true) ? $status : null;
    }

    /**
     * The statuses that appear in the recap summary and table.
     *
     * @return array<int, AttendanceStatus>
     */
    private function statusOptions(): array
    {
        return [
            AttendanceStatus::Hadir,
            AttendanceStatus::Terlambat,
            AttendanceStatus::Izin,
            AttendanceStatus::Sakit,
            AttendanceStatus::Alfa,
        ];
    }
}
