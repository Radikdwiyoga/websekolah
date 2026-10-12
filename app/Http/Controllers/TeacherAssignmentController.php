<?php

namespace App\Http\Controllers;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\SubmissionStatus;
use App\Http\Requests\Assignment\StoreAssignmentRequest;
use App\Http\Requests\Assignment\StoreQuestionsRequest;
use App\Http\Requests\Assignment\UpdateAssignmentRequest;
use App\Models\Assignment;
use App\Models\AssignmentQuestion;
use App\Models\AssignmentSubmission;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\AssignmentService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherAssignmentController extends Controller
{
    public function __construct(
        private readonly AssignmentService $assignments,
        private readonly SettingService $settings,
    ) {}

    public function index(Request $request): View
    {
        $teacher = $request->user()?->teacher;
        $classId = $request->integer('class_id') ?: null;
        $status = AssignmentStatus::tryFrom((string) $request->query('status', ''));

        return view('assignments.teacher.index', [
            'teacher' => $teacher,
            'assignments' => $teacher ? $this->assignments->teacherAssignments($teacher, $classId, $status) : collect(),
            'classes' => $teacher ? $this->assignments->teacherClasses($teacher) : collect(),
            'activeClassId' => $classId,
            'activeStatus' => $status,
            'statuses' => AssignmentStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('assignments.teacher.create', [
            'pairs' => $this->assignments->teacherSubjectPairs($request->user()->teacher),
            'types' => AssignmentType::cases(),
            'defaultGradeWeight' => (int) $this->settings->get('default_grade_weight', 100),
        ]);
    }

    public function store(StoreAssignmentRequest $request): RedirectResponse
    {
        $class = SchoolClass::findOrFail($request->validated('class_id'));
        $subject = Subject::findOrFail($request->validated('subject_id'));

        $this->authorize('createForClass', [Assignment::class, $class, $subject]);

        $assignment = $this->assignments->create($request->user()->teacher, $request->validated());

        return redirect()
            ->route('teacher.assignments.show', $assignment)
            ->with('success', 'Tugas disimpan sebagai draft. Tambahkan soal lalu publikasikan.');
    }

    public function show(Assignment $assignment): View
    {
        $this->authorize('view', $assignment);

        $assignment->load(['schoolClass', 'subject', 'questions.answers']);

        $submissions = $assignment->submissions()
            ->with('student.user')
            ->get()
            ->sortBy(fn (AssignmentSubmission $submission): string => (string) $submission->student?->user?->name)
            ->values();

        $activeStudents = $assignment->schoolClass->students()->where('is_active', true)->count();

        return view('assignments.teacher.show', [
            'assignment' => $assignment,
            'submissions' => $submissions,
            'stats' => [
                'students' => $activeStudents,
                'submitted' => $submissions
                    ->whereIn('status', [SubmissionStatus::Submitted, SubmissionStatus::Graded])
                    ->count(),
                'pending' => $submissions->where('status', SubmissionStatus::Submitted)->count(),
                'graded' => $submissions->where('status', SubmissionStatus::Graded)->count(),
            ],
        ]);
    }

    public function edit(Assignment $assignment): View|RedirectResponse
    {
        $this->authorize('update', $assignment);

        if (! $assignment->isDraft()) {
            return redirect()
                ->route('teacher.assignments.show', $assignment)
                ->with('error', 'Tugas yang sudah dipublikasikan tidak dapat diubah.');
        }

        return view('assignments.teacher.edit', [
            'assignment' => $assignment,
            'pairs' => $this->assignments->teacherSubjectPairs($assignment->teacher),
            'defaultGradeWeight' => (int) $this->settings->get('default_grade_weight', 100),
        ]);
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        if (! $assignment->isDraft()) {
            return back()->with('error', 'Tugas yang sudah dipublikasikan tidak dapat diubah.');
        }

        $class = SchoolClass::findOrFail($request->validated('class_id'));
        $subject = Subject::findOrFail($request->validated('subject_id'));

        $this->authorize('createForClass', [Assignment::class, $class, $subject]);

        $this->assignments->update($assignment, $request->validated());

        return redirect()
            ->route('teacher.assignments.show', $assignment)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $this->authorize('delete', $assignment);

        if (! $assignment->isDraft()) {
            return back()->with('error', 'Hanya draft yang dapat dihapus.');
        }

        $assignment->delete();

        return redirect()
            ->route('teacher.assignments.index')
            ->with('success', 'Draft tugas dihapus.');
    }

    public function publish(Assignment $assignment): RedirectResponse
    {
        $this->authorize('publish', $assignment);

        try {
            $this->assignments->publish($assignment);
        } catch (LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Tugas dipublikasikan ke siswa.');
    }

    public function close(Assignment $assignment): RedirectResponse
    {
        $this->authorize('close', $assignment);

        $this->assignments->close($assignment);

        return back()->with('success', 'Tugas ditutup. Siswa tidak dapat mengirim jawaban lagi.');
    }

    public function storeQuestions(StoreQuestionsRequest $request, Assignment $assignment): RedirectResponse
    {
        $created = $this->assignments->addQuestions($assignment, $request->validated('questions'));

        return back()->with('success', $created.' soal berhasil ditambahkan.');
    }

    public function destroyQuestion(Assignment $assignment, AssignmentQuestion $question): RedirectResponse
    {
        $this->authorize('manageQuestions', $assignment);

        abort_unless($question->assignment_id === $assignment->id, 404);

        $this->assignments->deleteQuestion($question);

        return back()->with('success', 'Soal berhasil dihapus.');
    }

    public function submissionFile(Assignment $assignment, AssignmentSubmission $submission): StreamedResponse
    {
        $this->authorize('grade', $assignment);

        abort_unless($submission->assignment_id === $assignment->id, 404);

        return $this->assignments->downloadFile($submission);
    }
}
