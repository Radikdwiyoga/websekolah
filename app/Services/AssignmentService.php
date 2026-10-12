<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\AssignmentQuestion;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use LogicException;

class AssignmentService
{
    public function __construct(private readonly SettingService $settings) {}

    public function activeSchoolYear(): ?SchoolYear
    {
        return SchoolYear::query()->where('is_active', true)->first()
            ?? SchoolYear::query()->latest('id')->first();
    }

    /**
     * Classes a teacher may attach assignments to: the classes they teach in
     * the active school year plus classes where they are the homeroom teacher.
     *
     * @return Collection<int, SchoolClass>
     */
    public function teacherClasses(Teacher $teacher): Collection
    {
        $year = $this->activeSchoolYear();

        $taClassIds = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->when($year, fn ($query) => $query->where('school_year_id', $year->id))
            ->pluck('class_id');

        $homeroomClassIds = SchoolClass::query()
            ->where('homeroom_teacher_id', $teacher->id)
            ->pluck('id');

        $ids = $taClassIds->merge($homeroomClassIds)->unique()->values()->all();

        if ($ids === []) {
            return collect();
        }

        return SchoolClass::query()->whereIn('id', $ids)->orderBy('name')->get();
    }

    /**
     * Subject/class pairs a teacher is allowed to create assignments for.
     *
     * @return Collection<int, array{class: SchoolClass, subject: Subject}>
     */
    public function teacherSubjectPairs(Teacher $teacher): Collection
    {
        $year = $this->activeSchoolYear();

        return TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->when($year, fn ($query) => $query->where('school_year_id', $year->id))
            ->with(['schoolClass', 'subject'])
            ->get()
            ->map(fn (TeachingAssignment $teaching): array => [
                'class' => $teaching->schoolClass,
                'subject' => $teaching->subject,
            ])
            ->filter(fn (array $pair): bool => $pair['class'] !== null && $pair['subject'] !== null)
            ->unique(fn (array $pair): string => $pair['class']->id.'-'.$pair['subject']->id)
            ->sortBy(fn (array $pair): string => $pair['class']->name.' '.$pair['subject']->name)
            ->values();
    }

    /**
     * @return Collection<int, Assignment>
     */
    public function teacherAssignments(Teacher $teacher, ?int $classId = null, ?AssignmentStatus $status = null): Collection
    {
        $year = $this->activeSchoolYear();

        return Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->when($year, fn ($query) => $query->where('school_year_id', $year->id))
            ->when($classId, fn ($query) => $query->where('class_id', $classId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with(['schoolClass', 'subject'])
            ->withCount([
                'submissions',
                'submissions as graded_count' => fn ($query) => $query->where('status', SubmissionStatus::Graded),
                'submissions as pending_count' => fn ($query) => $query->where('status', SubmissionStatus::Submitted),
            ])
            ->latest()
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Teacher $teacher, array $data): Assignment
    {
        $year = $this->activeSchoolYear();

        if (! $year) {
            throw new LogicException('Belum ada tahun ajaran aktif. Hubungi administrator.');
        }

        return Assignment::create([
            'teacher_id' => $teacher->id,
            'school_year_id' => $year->id,
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'deadline' => $data['deadline'] ?? null,
            'grade_weight' => $data['grade_weight'] ?? 100,
            'late_deduction_percent' => $data['late_deduction_percent'] ?? 0,
            'allow_resubmit' => $data['allow_resubmit'] ?? true,
            'status' => AssignmentStatus::Draft,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Assignment $assignment, array $data): Assignment
    {
        $assignment->update([
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'grade_weight' => $data['grade_weight'] ?? 100,
            'late_deduction_percent' => $data['late_deduction_percent'] ?? 0,
            'allow_resubmit' => $data['allow_resubmit'] ?? false,
        ]);

        return $assignment->refresh();
    }

    public function publish(Assignment $assignment): void
    {
        if ($assignment->type !== AssignmentType::FileUpload && $assignment->questions()->count() === 0) {
            throw new LogicException('Tambahkan minimal satu soal sebelum mempublikasikan tugas.');
        }

        $assignment->update(['status' => AssignmentStatus::Published]);
    }

    public function close(Assignment $assignment): void
    {
        $assignment->update(['status' => AssignmentStatus::Closed]);
    }

    /**
     * Append question rows to a draft assignment.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function addQuestions(Assignment $assignment, array $rows): int
    {
        $order = (int) $assignment->questions()->max('order');
        $created = 0;

        DB::transaction(function () use ($assignment, $rows, $order, &$created): void {
            foreach ($rows as $row) {
                if (trim((string) ($row['question_text'] ?? '')) === '') {
                    continue;
                }

                $order++;

                $assignment->questions()->create([
                    'order' => $order,
                    'type' => $assignment->type,
                    'question_text' => $row['question_text'],
                    'options' => $this->optionsFor($assignment->type, $row),
                    'correct_answers' => $this->correctAnswersFor($assignment->type, $row),
                    'points' => $row['points'] ?? 1,
                ]);

                $created++;
            }
        });

        return $created;
    }

    public function deleteQuestion(AssignmentQuestion $question): void
    {
        $question->delete();
    }

    /**
     * @return Collection<int, Assignment>
     */
    public function studentAssignments(Student $student): Collection
    {
        $year = $this->activeSchoolYear();

        return Assignment::query()
            ->where('class_id', $student->class_id)
            ->when($year, fn ($query) => $query->where('school_year_id', $year->id))
            ->where('status', AssignmentStatus::Published)
            ->with('subject')
            ->with(['submissions' => fn ($query) => $query->where('student_id', $student->id)])
            ->latest()
            ->get();
    }

    public function submissionFor(Assignment $assignment, Student $student): ?AssignmentSubmission
    {
        return $assignment->submissions()->where('student_id', $student->id)->first();
    }

    /**
     * Create or update a student's submission and, when finalising, run the
     * automatic grading for multiple choice and short answer questions.
     *
     * @param  array<int|string, string|null>  $answers  answer per question id
     */
    public function saveSubmission(
        Assignment $assignment,
        Student $student,
        array $answers,
        ?UploadedFile $file,
        bool $finalize,
    ): AssignmentSubmission {
        if (! $assignment->isPublished()) {
            throw new LogicException('Tugas ini belum dipublikasikan.');
        }

        if ($assignment->class_id !== $student->class_id) {
            throw new LogicException('Tugas ini bukan untuk kelas Anda.');
        }

        $submission = $assignment->submissions()->firstOrNew(['student_id' => $student->id]);

        if ($submission->exists && $submission->status !== SubmissionStatus::Draft && ! $assignment->allow_resubmit) {
            throw new LogicException('Pengiriman ulang tidak diizinkan untuk tugas ini.');
        }

        if ($finalize && $assignment->type === AssignmentType::FileUpload && ! $file && ! $submission->file_path) {
            throw new LogicException('Unggah berkas jawaban sebelum mengirim.');
        }

        DB::transaction(function () use ($assignment, $student, $answers, $file, $finalize, $submission): void {
            if ($file) {
                $submission->file_path = $file->store("assignments/{$assignment->id}/{$student->id}", 'local');
            }

            $submission->assignment_id = $assignment->id;
            $submission->student_id = $student->id;
            $submission->status = $finalize ? SubmissionStatus::Submitted : SubmissionStatus::Draft;

            if ($finalize) {
                $submission->submitted_at = now();
                $submission->is_late = $assignment->isOverdue();
            }

            $submission->save();

            $this->storeAnswers($assignment, $submission, $answers);

            if ($finalize) {
                $this->autoGrade($submission);
            }
        });

        return $submission->refresh();
    }

    /**
     * Score every automatically graded answer and settle the submission status.
     */
    public function autoGrade(AssignmentSubmission $submission): void
    {
        $assignment = $submission->assignment()->with('questions')->first();

        if (! $assignment) {
            return;
        }

        $requiresManual = $assignment->type === AssignmentType::FileUpload;

        foreach ($submission->answers()->with('question')->get() as $answer) {
            $question = $answer->question;

            if (! $question) {
                continue;
            }

            if (! $question->type->isAutoGraded()) {
                $requiresManual = true;
                $answer->update(['is_correct' => null, 'score' => null]);

                continue;
            }

            $isCorrect = $this->isAnswerCorrect($question, $answer->answer_text);

            $answer->update([
                'is_correct' => $isCorrect,
                'score' => $isCorrect ? $question->points : 0,
            ]);
        }

        if ($requiresManual) {
            $submission->update([
                'status' => SubmissionStatus::Submitted,
                'total_score' => null,
                'graded_at' => null,
                'graded_by' => null,
            ]);

            return;
        }

        $submission->update([
            'status' => SubmissionStatus::Graded,
            'total_score' => $this->computeScore($submission),
            'graded_at' => now(),
            'graded_by' => null,
        ]);
    }

    /**
     * Apply the teacher's manual scoring and finalise the submission.
     *
     * @param  array<int|string, float|int|string|null>  $scores  score per answer id
     */
    public function grade(
        AssignmentSubmission $submission,
        User $gradedBy,
        array $scores,
        float|int|string|null $overrideTotal,
        ?string $comment,
    ): void {
        DB::transaction(function () use ($submission, $gradedBy, $scores, $overrideTotal, $comment): void {
            $submission->load(['answers.question', 'assignment']);

            foreach ($submission->answers as $answer) {
                $raw = $scores[(string) $answer->id] ?? $scores[$answer->id] ?? null;

                if ($raw === null || $raw === '') {
                    continue;
                }

                $points = (float) ($answer->question?->points ?? 0);

                $answer->update([
                    'score' => min((float) $raw, $points),
                    'is_correct' => $answer->question && $answer->question->type->isAutoGraded()
                        ? ((float) $raw >= $points && $points > 0)
                        : null,
                ]);
            }

            $submission->update([
                'total_score' => $this->computeScore($submission),
                'teacher_score' => ($overrideTotal === null || $overrideTotal === '') ? null : (float) $overrideTotal,
                'teacher_comment' => $comment,
                'status' => SubmissionStatus::Graded,
                'graded_by' => $gradedBy->id,
                'graded_at' => now(),
            ]);

            $this->syncGradeRecord($submission, $gradedBy);
        });
    }

    /**
     * @param  array<int|string, string|null>  $answers
     */
    private function storeAnswers(Assignment $assignment, AssignmentSubmission $submission, array $answers): void
    {
        $submission->answers()->delete();

        foreach ($assignment->questions()->get() as $question) {
            $value = $answers[(string) $question->id] ?? $answers[$question->id] ?? null;
            $value = is_string($value) ? trim($value) : $value;

            $submission->answers()->create([
                'assignment_question_id' => $question->id,
                'answer_text' => ($value === '' ? null : $value),
            ]);
        }
    }

    private function isAnswerCorrect(AssignmentQuestion $question, ?string $answer): bool
    {
        if ($answer === null || trim($answer) === '') {
            return false;
        }

        $correct = collect($question->correct_answers ?? []);

        if ($question->type === AssignmentType::MultipleChoice) {
            return $correct->contains(
                fn ($value): bool => strtoupper(trim((string) $value)) === strtoupper(trim($answer)),
            );
        }

        $normalized = $this->normalize($answer);

        return $correct->contains(fn ($value): bool => $this->normalize((string) $value) === $normalized);
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    /**
     * Sum every answer score and apply the late deduction when it applies.
     */
    private function computeScore(AssignmentSubmission $submission): float
    {
        $submission->loadMissing('assignment');

        $raw = (float) $submission->answers()->sum('score');
        $deduction = $submission->is_late
            ? (int) ($submission->assignment->late_deduction_percent ?? 0)
            : 0;

        return round(max(0, $raw * (1 - $deduction / 100)), 2);
    }

    private function syncGradeRecord(AssignmentSubmission $submission, User $gradedBy): void
    {
        $assignment = $submission->assignment;

        Grade::query()->updateOrCreate(
            ['student_id' => $submission->student_id, 'assignment_id' => $submission->assignment_id],
            [
                'subject_id' => $assignment->subject_id,
                'class_id' => $assignment->class_id,
                'school_year_id' => $assignment->school_year_id,
                'score' => $submission->teacher_score ?? $submission->total_score ?? 0,
                'weight' => $assignment->grade_weight,
                'type' => 'assignment',
                'graded_by' => $gradedBy->id,
                'graded_at' => now(),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, string>|null
     */
    private function optionsFor(AssignmentType $type, array $row): ?array
    {
        if ($type !== AssignmentType::MultipleChoice) {
            return null;
        }

        return collect($row['options'] ?? [])
            ->mapWithKeys(fn ($value, $key): array => [strtoupper((string) $key) => trim((string) $value)])
            ->filter(fn (string $value): bool => $value !== '')
            ->all();
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<int, string>|null
     */
    private function correctAnswersFor(AssignmentType $type, array $row): ?array
    {
        return match ($type) {
            AssignmentType::MultipleChoice => [strtoupper((string) ($row['correct_answer'] ?? 'A'))],
            AssignmentType::ShortAnswer => collect(preg_split('/[\r\n]+/', (string) ($row['correct_answers'] ?? '')))
                ->map(fn (string $answer): string => trim($answer))
                ->filter()
                ->values()
                ->all(),
            default => null,
        };
    }

    /**
     * Stream a stored submission file to an authorised user.
     */
    public function downloadFile(AssignmentSubmission $submission)
    {
        if (! $submission->file_path || ! Storage::disk('local')->exists($submission->file_path)) {
            throw new LogicException('Berkas jawaban tidak ditemukan.');
        }

        return Storage::disk('local')->download(
            $submission->file_path,
            basename($submission->file_path),
        );
    }

    /**
     * @return array{raw: float|null, points: float, manual: bool}
     */
    public function gradingSummary(AssignmentSubmission $submission): array
    {
        $submission->loadMissing(['answers.question', 'assignment.questions']);

        $maxPoints = (float) $submission->assignment->questions()->sum('points');

        return [
            'raw' => $submission->total_score !== null ? (float) $submission->total_score : null,
            'points' => $maxPoints,
            'manual' => (bool) $submission->assignment->questions
                ->contains(fn (AssignmentQuestion $question): bool => ! $question->type->isAutoGraded()),
        ];
    }

    /**
     * The score actually shown to students and parents.
     */
    public function effectiveScore(AssignmentSubmission $submission): ?float
    {
        $score = $submission->teacher_score ?? $submission->total_score;

        return $score === null ? null : round((float) $score, 2);
    }
}
