<?php

namespace Tests\Feature\Assignment;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\AssignmentQuestion;
use App\Models\AssignmentSubmission;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_index_lists_published_assignments_for_their_class(): void
    {
        [$assignment, $studentUser, $student, $class, $teacher] = $this->makeStudentSetup();

        // Draft and other-class assignments must not appear.
        Assignment::factory()->create(['status' => AssignmentStatus::Draft]);
        Assignment::factory()->published()->create();

        $this->actingAs($studentUser)
            ->get(route('student.assignments.index'))
            ->assertOk()
            ->assertSee($assignment->title);
    }

    public function test_student_cannot_view_a_draft_assignment(): void
    {
        [$assignment, $studentUser] = $this->makeStudentSetup();

        $assignment->update(['status' => AssignmentStatus::Draft]);

        $this->actingAs($studentUser)
            ->get(route('student.assignments.show', $assignment))
            ->assertForbidden();
    }

    public function test_multiple_choice_answers_are_graded_automatically(): void
    {
        [$assignment, $studentUser, $student] = $this->makeStudentSetup();

        $first = AssignmentQuestion::factory()->create([
            'assignment_id' => $assignment->id,
            'order' => 1,
            'type' => AssignmentType::MultipleChoice,
            'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'],
            'correct_answers' => ['B'],
            'points' => 50,
        ]);

        $second = AssignmentQuestion::factory()->create([
            'assignment_id' => $assignment->id,
            'order' => 2,
            'type' => AssignmentType::MultipleChoice,
            'options' => ['A' => 'x', 'B' => 'y', 'C' => 'z', 'D' => 'w'],
            'correct_answers' => ['A'],
            'points' => 50,
        ]);

        $this->actingAs($studentUser)
            ->post(route('student.assignments.save', $assignment), [
                'finalize' => 1,
                'answers' => [
                    $first->id => 'B',
                    $second->id => 'A',
                ],
            ])
            ->assertRedirect(route('student.assignments.show', $assignment));

        $submission = AssignmentSubmission::query()->where('student_id', $student->id)->firstOrFail();

        $this->assertSame(SubmissionStatus::Graded, $submission->status);
        $this->assertEquals(100, (float) $submission->total_score);
        $this->assertFalse($submission->is_late);
    }

    public function test_short_answer_grading_ignores_letter_case(): void
    {
        [$assignment, $studentUser, $student] = $this->makeStudentSetup();

        $question = AssignmentQuestion::factory()->create([
            'assignment_id' => $assignment->id,
            'order' => 1,
            'type' => AssignmentType::ShortAnswer,
            'options' => null,
            'correct_answers' => ['Jakarta'],
            'points' => 100,
        ]);

        $this->actingAs($studentUser)->post(route('student.assignments.save', $assignment), [
            'finalize' => 1,
            'answers' => [$question->id => '  jakarta '],
        ])->assertRedirect();

        $submission = AssignmentSubmission::query()->where('student_id', $student->id)->firstOrFail();

        $this->assertSame(SubmissionStatus::Graded, $submission->status);
        $this->assertEquals(100, (float) $submission->total_score);
    }

    public function test_essay_submission_waits_for_manual_grading(): void
    {
        [$assignment, $studentUser, $student] = $this->makeStudentSetup(AssignmentType::Essay);

        $question = AssignmentQuestion::factory()->essay()->create([
            'assignment_id' => $assignment->id,
            'order' => 1,
            'points' => 100,
        ]);

        $this->actingAs($studentUser)->post(route('student.assignments.save', $assignment), [
            'finalize' => 1,
            'answers' => [$question->id => 'Menurut saya konsep ini berarti ...'],
        ])->assertRedirect();

        $submission = AssignmentSubmission::query()->where('student_id', $student->id)->firstOrFail();

        $this->assertSame(SubmissionStatus::Submitted, $submission->status);
        $this->assertNull($submission->total_score);
    }

    public function test_late_submission_is_flagged_and_deducted(): void
    {
        [$assignment, $studentUser, $student] = $this->makeStudentSetup(
            AssignmentType::MultipleChoice,
            [
                'deadline' => now()->subDay(),
                'late_deduction_percent' => 50,
                'allow_resubmit' => true,
            ],
        );

        $question = AssignmentQuestion::factory()->create([
            'assignment_id' => $assignment->id,
            'order' => 1,
            'type' => AssignmentType::MultipleChoice,
            'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'],
            'correct_answers' => ['A'],
            'points' => 100,
        ]);

        $this->actingAs($studentUser)->post(route('student.assignments.save', $assignment), [
            'finalize' => 1,
            'answers' => [$question->id => 'A'],
        ])->assertRedirect();

        $submission = AssignmentSubmission::query()->where('student_id', $student->id)->firstOrFail();

        $this->assertTrue($submission->is_late);
        $this->assertSame(SubmissionStatus::Graded, $submission->status);
        $this->assertEquals(50, (float) $submission->total_score);
    }

    public function test_resubmission_is_blocked_when_not_allowed(): void
    {
        [$assignment, $studentUser, $student] = $this->makeStudentSetup(
            AssignmentType::MultipleChoice,
            ['allow_resubmit' => false],
        );

        $question = AssignmentQuestion::factory()->create([
            'assignment_id' => $assignment->id,
            'order' => 1,
            'type' => AssignmentType::MultipleChoice,
            'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'],
            'correct_answers' => ['A'],
            'points' => 100,
        ]);

        $this->actingAs($studentUser)->post(route('student.assignments.save', $assignment), [
            'finalize' => 1,
            'answers' => [$question->id => 'A'],
        ])->assertRedirect();

        $this->actingAs($studentUser)->post(route('student.assignments.save', $assignment), [
            'finalize' => 1,
            'answers' => [$question->id => 'B'],
        ])->assertForbidden();
    }

    public function test_file_upload_requires_a_file(): void
    {
        Storage::fake('local');

        [$assignment, $studentUser, $student] = $this->makeStudentSetup(AssignmentType::FileUpload);

        $this->actingAs($studentUser)
            ->post(route('student.assignments.save', $assignment), ['finalize' => 1])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('assignment_submissions', ['student_id' => $student->id]);
    }

    public function test_student_can_upload_a_file_submission(): void
    {
        Storage::fake('local');

        [$assignment, $studentUser, $student] = $this->makeStudentSetup(AssignmentType::FileUpload);

        $this->actingAs($studentUser)->post(route('student.assignments.save', $assignment), [
            'finalize' => 1,
            'file' => UploadedFile::fake()->create('jawaban.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $submission = AssignmentSubmission::query()->where('student_id', $student->id)->firstOrFail();

        $this->assertSame(SubmissionStatus::Submitted, $submission->status);
        $this->assertNotNull($submission->file_path);
        Storage::disk('local')->assertExists($submission->file_path);
    }

    public function test_student_can_open_the_work_page(): void
    {
        [$assignment, $studentUser] = $this->makeStudentSetup();

        AssignmentQuestion::factory()->create([
            'assignment_id' => $assignment->id,
            'order' => 1,
            'type' => AssignmentType::MultipleChoice,
            'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'],
            'correct_answers' => ['A'],
            'points' => 100,
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.assignments.show', $assignment))
            ->assertOk()
            ->assertSee($assignment->title)
            ->assertSee('Kirim Jawaban');
    }

    public function test_graded_submission_is_shown_read_only_with_the_score(): void
    {
        [$assignment, $studentUser, $student] = $this->makeStudentSetup(
            AssignmentType::MultipleChoice,
            ['allow_resubmit' => false],
        );

        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submitted_at' => now(),
            'status' => SubmissionStatus::Graded,
            'total_score' => 80,
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.assignments.show', $assignment))
            ->assertOk()
            ->assertSee('Nilai Anda')
            ->assertSee('Jawaban Anda')
            ->assertDontSee('Kirim Jawaban');
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{0: Assignment, 1: User, 2: Student, 3: SchoolClass, 4: Teacher}
     */
    private function makeStudentSetup(
        AssignmentType $type = AssignmentType::MultipleChoice,
        array $attributes = [],
    ): array {
        $year = SchoolYear::factory()->active()->create(['name' => '2026/2027']);
        $teacherUser = User::factory()->asTeacher()->create();
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
        $class = SchoolClass::factory()->create(['school_year_id' => $year->id]);
        $subject = Subject::factory()->create(['name' => 'Matematika']);

        TeachingAssignment::factory()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
        ]);

        $studentUser = User::factory()->asStudent()->create();
        $student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
        ]);

        $assignment = Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'type' => $type,
            'deadline' => now()->addDays(3),
            'allow_resubmit' => true,
            ...$attributes,
        ]);

        return [$assignment, $studentUser, $student, $class, $teacher];
    }
}
