<?php

namespace App\Http\Controllers;

use App\Enums\SubmissionStatus;
use App\Http\Requests\Assignment\GradeSubmissionRequest;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Services\AssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherGradeController extends Controller
{
    public function __construct(private readonly AssignmentService $assignments) {}

    public function index(Request $request): View
    {
        $teacher = $request->user()?->teacher;

        if (! $teacher) {
            return view('grades.teacher.index', [
                'pending' => collect(),
                'recent' => collect(),
            ]);
        }

        $assignmentIds = Assignment::query()->where('teacher_id', $teacher->id)->pluck('id');

        $pending = AssignmentSubmission::query()
            ->whereIn('assignment_id', $assignmentIds)
            ->where('status', SubmissionStatus::Submitted)
            ->with(['assignment.subject', 'assignment.schoolClass', 'student.user'])
            ->oldest('submitted_at')
            ->get();

        $recent = AssignmentSubmission::query()
            ->whereIn('assignment_id', $assignmentIds)
            ->where('status', SubmissionStatus::Graded)
            ->with(['assignment.subject', 'assignment.schoolClass', 'student.user'])
            ->latest('graded_at')
            ->limit(10)
            ->get();

        return view('grades.teacher.index', [
            'teacher' => $teacher,
            'pending' => $pending,
            'recent' => $recent,
        ]);
    }

    public function edit(AssignmentSubmission $submission): View
    {
        $this->authorize('grade', $submission->assignment);

        $submission->load([
            'assignment.subject',
            'assignment.schoolClass',
            'assignment.questions',
            'student.user',
            'answers.question',
        ]);

        return view('grades.teacher.edit', [
            'submission' => $submission,
            'assignment' => $submission->assignment,
            'answers' => $submission->answers->keyBy('assignment_question_id'),
            'effectiveScore' => $this->assignments->effectiveScore($submission),
        ]);
    }

    public function update(GradeSubmissionRequest $request, AssignmentSubmission $submission): RedirectResponse
    {
        $this->assignments->grade(
            $submission,
            $request->user(),
            $request->validated('scores', []),
            $request->validated('teacher_score'),
            $request->validated('teacher_comment'),
        );

        return redirect()
            ->route('teacher.grades.index')
            ->with('success', 'Nilai berhasil disimpan dan diteruskan ke rekap.');
    }
}
