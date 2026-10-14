<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AssignmentGradeBackupService
{
    /**
     * Build a summary backup of assignments (tugas) and grades (penilaian).
     *
     * @param  array<int, int>  $classIds
     * @return array{
     *     classes: Collection<int, SchoolClass>,
     *     subjects: Collection<int, Subject>,
     *     selectedClassId: int|null,
     *     selectedSubjectId: int|null,
     *     start: Carbon|null,
     *     end: Carbon|null,
     *     rangeLabel: string,
     *     className: string,
     *     subjectName: string,
     *     summary: array{assignments: int, submissions: int, graded: int, average: float|null},
     *     assignmentRows: Collection<int, array{title: string, class: string, subject: string, teacher: string, type: string, status: string, deadline: string, students: int, submitted: int, graded: int, average: string}>,
     *     studentRows: Collection<int, array{name: string, class: string, assignments: int, submitted: int, graded: int, average: string}>,
     *     grades: Collection<int, array{student: string, class: string, subject: string, assignment: string, score: string, weight: string, graded_at: string, graded_by: string}>
     * }
     */
    public function build(array $classIds, ?int $classId, ?int $subjectId, ?string $startDate, ?string $endDate): array
    {
        [$start, $end, $rangeLabel] = $this->resolveRange($startDate, $endDate);

        $classes = SchoolClass::query()
            ->whereIn('id', $classIds)
            ->orderBy('name')
            ->get();

        $subjects = Subject::query()->orderBy('name')->get();

        $scope = $this->resolveScope($classIds, $classId);
        $selectedClass = $classId !== null ? $classes->firstWhere('id', $classId) : null;
        $selectedSubject = $subjectId !== null ? $subjects->firstWhere('id', $subjectId) : null;

        $classStudentCounts = Student::query()
            ->whereIn('class_id', $scope)
            ->where('is_active', true)
            ->selectRaw('class_id, count(*) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $assignments = Assignment::query()
            ->whereIn('class_id', $scope)
            ->when($subjectId !== null, fn ($query) => $query->where('subject_id', $subjectId))
            ->when($start !== null, fn ($query) => $query->where('created_at', '>=', $start->copy()->startOfDay()))
            ->when($end !== null, fn ($query) => $query->where('created_at', '<=', $end->copy()->endOfDay()))
            ->with(['teacher.user', 'schoolClass', 'subject', 'submissions'])
            ->orderBy('class_id')
            ->orderBy('subject_id')
            ->orderBy('title')
            ->get();

        $assignmentRows = $assignments->map(function (Assignment $assignment) use ($classStudentCounts): array {
            $submissions = $assignment->submissions;
            $submitted = $submissions->filter(fn (AssignmentSubmission $submission): bool => $submission->submitted_at !== null);
            $graded = $submissions->filter(fn (AssignmentSubmission $submission): bool => $submission->graded_at !== null);

            return [
                'title' => $assignment->title,
                'class' => $assignment->schoolClass?->name ?? '—',
                'subject' => $assignment->subject?->name ?? '—',
                'teacher' => $assignment->teacher?->user?->name ?? '—',
                'type' => $assignment->type?->label() ?? '—',
                'status' => $assignment->status?->label() ?? '—',
                'deadline' => $assignment->deadline?->translatedFormat('d M Y H.i') ?? '—',
                'students' => (int) ($classStudentCounts[$assignment->class_id] ?? 0),
                'submitted' => $submitted->count(),
                'graded' => $graded->count(),
                'average' => $this->formatAverage($this->averageScore($graded)),
            ];
        })->values();

        $assignmentIds = $assignments->pluck('id');
        $submissions = AssignmentSubmission::query()
            ->whereIn('assignment_id', $assignmentIds)
            ->with(['student.user', 'student.schoolClass'])
            ->get();

        $students = Student::query()
            ->whereIn('class_id', $scope)
            ->where('is_active', true)
            ->with(['user', 'schoolClass'])
            ->get()
            ->sortBy(fn (Student $student): string => ($student->schoolClass?->name ?? '').'|'.($student->user?->name ?? ''))
            ->values();

        $submissionsByStudent = $submissions->groupBy('student_id');

        $studentRows = $students->map(function (Student $student) use ($assignments, $submissionsByStudent): array {
            $studentSubmissions = $submissionsByStudent->get($student->id, collect());
            $submitted = $studentSubmissions->filter(fn (AssignmentSubmission $submission): bool => $submission->submitted_at !== null);
            $graded = $studentSubmissions->filter(fn (AssignmentSubmission $submission): bool => $submission->graded_at !== null);

            return [
                'name' => $student->user?->name ?? '—',
                'class' => $student->schoolClass?->name ?? '—',
                'assignments' => $assignments->where('class_id', $student->class_id)->count(),
                'submitted' => $submitted->count(),
                'graded' => $graded->count(),
                'average' => $this->formatAverage($this->averageScore($graded)),
            ];
        })->values();

        $grades = Grade::query()
            ->whereIn('class_id', $scope)
            ->when($subjectId !== null, fn ($query) => $query->where('subject_id', $subjectId))
            ->when($start !== null, fn ($query) => $query->where('graded_at', '>=', $start->copy()->startOfDay()))
            ->when($end !== null, fn ($query) => $query->where('graded_at', '<=', $end->copy()->endOfDay()))
            ->with(['student.user', 'schoolClass', 'subject', 'assignment', 'gradedBy'])
            ->orderBy('class_id')
            ->orderBy('subject_id')
            ->orderBy('student_id')
            ->get()
            ->map(fn (Grade $grade): array => [
                'student' => $grade->student?->user?->name ?? '—',
                'class' => $grade->schoolClass?->name ?? '—',
                'subject' => $grade->subject?->name ?? '—',
                'assignment' => $grade->assignment?->title ?? '—',
                'score' => $this->formatAverage($grade->score === null ? null : (float) $grade->score),
                'weight' => $this->formatAverage($grade->weight === null ? null : (float) $grade->weight),
                'graded_at' => $grade->graded_at?->translatedFormat('d M Y H.i') ?? '—',
                'graded_by' => $grade->gradedBy?->name ?? '—',
            ]);

        return [
            'classes' => $classes,
            'subjects' => $subjects,
            'selectedClassId' => $selectedClass?->id,
            'selectedSubjectId' => $selectedSubject?->id,
            'start' => $start,
            'end' => $end,
            'rangeLabel' => $rangeLabel,
            'className' => $selectedClass?->name ?? 'Semua Kelas',
            'subjectName' => $selectedSubject?->name ?? 'Semua Mata Pelajaran',
            'summary' => [
                'assignments' => $assignments->count(),
                'submissions' => $submissions->filter(fn (AssignmentSubmission $submission): bool => $submission->submitted_at !== null)->count(),
                'graded' => $submissions->filter(fn (AssignmentSubmission $submission): bool => $submission->graded_at !== null)->count(),
                'average' => $this->averageScore($submissions->filter(fn (AssignmentSubmission $submission): bool => $submission->graded_at !== null)),
            ],
            'assignmentRows' => $assignmentRows,
            'studentRows' => $studentRows,
            'grades' => $grades->values(),
        ];
    }

    /**
     * @param  Collection<int, AssignmentSubmission>  $submissions
     */
    private function averageScore(Collection $submissions): ?float
    {
        $scores = $submissions->map(function (AssignmentSubmission $submission): ?float {
            $score = $submission->teacher_score ?? $submission->total_score;

            return $score === null ? null : (float) $score;
        })->filter(fn (?float $score): bool => $score !== null);

        if ($scores->isEmpty()) {
            return null;
        }

        return round((float) $scores->avg(), 2);
    }

    private function formatAverage(?float $value): string
    {
        return $value === null ? '—' : number_format($value, 2, ',', '.');
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
     * @return array{0: Carbon|null, 1: Carbon|null, 2: string}
     */
    private function resolveRange(?string $startDate, ?string $endDate): array
    {
        $start = $this->parseDate($startDate);
        $end = $this->parseDate($endDate);

        $label = match (true) {
            $start !== null && $end !== null => $start->translatedFormat('d M Y').' – '.$end->translatedFormat('d M Y'),
            $start !== null => 'Sejak '.$start->translatedFormat('d M Y'),
            $end !== null => 'Hingga '.$end->translatedFormat('d M Y'),
            default => 'Semua tanggal',
        };

        return [$start, $end, $label];
    }

    private function parseDate(?string $date): ?Carbon
    {
        if ($date === null || $date === '') {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception) {
            return null;
        }
    }
}
