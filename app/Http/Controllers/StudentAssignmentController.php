<?php

namespace App\Http\Controllers;

use App\Enums\SubmissionStatus;
use App\Http\Requests\Assignment\SubmitAssignmentRequest;
use App\Models\Assignment;
use App\Services\AssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentAssignmentController extends Controller
{
    public function __construct(private readonly AssignmentService $assignments) {}

    public function index(Request $request): View
    {
        $student = $request->user()?->student;

        return view('assignments.student.index', [
            'student' => $student,
            'assignments' => $student ? $this->assignments->studentAssignments($student) : collect(),
        ]);
    }

    public function show(Assignment $assignment, Request $request): View
    {
        $this->authorize('view', $assignment);

        $student = $request->user()->student;

        $assignment->load(['subject', 'schoolClass', 'questions']);

        $submission = $student ? $this->assignments->submissionFor($assignment, $student) : null;

        $answers = $submission
            ? $submission->answers()->get()->keyBy('assignment_question_id')
            : collect();

        return view('assignments.student.show', [
            'assignment' => $assignment,
            'submission' => $submission,
            'answers' => $answers,
            'effectiveScore' => $submission ? $this->assignments->effectiveScore($submission) : null,
        ]);
    }

    public function save(SubmitAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $finalize = $request->boolean('finalize');

        try {
            $submission = $this->assignments->saveSubmission(
                $assignment,
                $request->user()->student,
                $request->validated('answers', []),
                $request->file('file'),
                $finalize,
            );
        } catch (LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $message = match (true) {
            ! $finalize => 'Jawaban disimpan sebagai draft.',
            $submission->status === SubmissionStatus::Graded => 'Jawaban terkirim dan dinilai otomatis.',
            default => 'Jawaban terkirim. Menunggu penilaian guru.',
        };

        return redirect()
            ->route('student.assignments.show', $assignment)
            ->with('success', $message);
    }

    public function file(Assignment $assignment, Request $request): StreamedResponse
    {
        $this->authorize('view', $assignment);

        $student = $request->user()->student;
        abort_unless($student, 403);

        $submission = $this->assignments->submissionFor($assignment, $student);
        abort_unless($submission, 404);

        return $this->assignments->downloadFile($submission);
    }
}
