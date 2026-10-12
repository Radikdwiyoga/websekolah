<?php

namespace Tests\Feature\Assignment;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\AssignmentAnswer;
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
use Tests\TestCase;

class TeacherGradingTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_submissions_are_listed_for_the_teacher(): void
    {
        [$assignment, $teacherUser, $teacher, $class, $subject, $student] = $this->makeGradingSetup(AssignmentType::Essay);

        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => SubmissionStatus::Submitted,
        ]);

        $this->actingAs($teacherUser)
            ->get(route('teacher.grades.index'))
            ->assertOk()
            ->assertSee($student->user->name)
            ->assertSee($assignment->title);
    }

    public function test_teacher_can_grade_an_essay_and_store_a_grade_record(): void
    {
        [$assignment, $teacherUser, $teacher, $class, $subject, $student] = $this->makeGradingSetup(AssignmentType::Essay);

        $question = AssignmentQuestion::factory()->essay()->create([
            'assignment_id' => $assignment->id,
            'order' => 1,
            'points' => 100,
        ]);

        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => SubmissionStatus::Submitted,
        ]);

        $answer = AssignmentAnswer::factory()->create([
            'assignment_submission_id' => $submission->id,
            'assignment_question_id' => $question->id,
            'answer_text' => 'Jawaban esai siswa.',
        ]);

        $this->actingAs($teacherUser)
            ->put(route('teacher.grades.update', $submission), [
                'scores' => [$answer->id => 80],
                'teacher_comment' => 'Analisis sudah baik.',
            ])
            ->assertRedirect(route('teacher.grades.index'));

        $submission->refresh();

        $this->assertSame(SubmissionStatus::Graded, $submission->status);
        $this->assertEquals(80, (float) $submission->total_score);
        $this->assertSame('Analisis sudah baik.', $submission->teacher_comment);
        $this->assertSame($teacherUser->id, $submission->graded_by);

        $this->assertDatabaseHas('grades', [
            'student_id' => $student->id,
            'assignment_id' => $assignment->id,
            'score' => 80,
        ]);
    }

    public function test_teacher_score_override_is_used_as_the_effective_score(): void
    {
        [$assignment, $teacherUser, $teacher, $class, $subject, $student] = $this->makeGradingSetup(AssignmentType::Essay);

        $question = AssignmentQuestion::factory()->essay()->create([
            'assignment_id' => $assignment->id,
            'order' => 1,
            'points' => 100,
        ]);

        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => SubmissionStatus::Submitted,
        ]);

        $answer = AssignmentAnswer::factory()->create([
            'assignment_submission_id' => $submission->id,
            'assignment_question_id' => $question->id,
            'answer_text' => 'Jawaban esai siswa.',
        ]);

        $this->actingAs($teacherUser)->put(route('teacher.grades.update', $submission), [
            'scores' => [$answer->id => 60],
            'teacher_score' => 90,
        ])->assertRedirect();

        $submission->refresh();

        $this->assertEquals(90, (float) $submission->teacher_score);
        $this->assertDatabaseHas('grades', [
            'student_id' => $student->id,
            'assignment_id' => $assignment->id,
            'score' => 90,
        ]);
    }

    public function test_teacher_cannot_grade_another_teachers_submission(): void
    {
        [$assignment, $teacherUser, $teacher, $class, $subject, $student] = $this->makeGradingSetup(AssignmentType::Essay);

        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => SubmissionStatus::Submitted,
        ]);

        $otherTeacherUser = User::factory()->asTeacher()->create();
        Teacher::factory()->create(['user_id' => $otherTeacherUser->id]);

        $this->actingAs($otherTeacherUser)
            ->get(route('teacher.grades.edit', $submission))
            ->assertForbidden();
    }

    public function test_teacher_can_open_the_grading_page(): void
    {
        [$assignment, $teacherUser, $teacher, $class, $subject, $student] = $this->makeGradingSetup(AssignmentType::Essay);

        $question = AssignmentQuestion::factory()->essay()->create([
            'assignment_id' => $assignment->id,
            'order' => 1,
            'points' => 100,
        ]);

        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => SubmissionStatus::Submitted,
        ]);

        AssignmentAnswer::factory()->create([
            'assignment_submission_id' => $submission->id,
            'assignment_question_id' => $question->id,
            'answer_text' => 'Jawaban esai siswa.',
        ]);

        $this->actingAs($teacherUser)
            ->get(route('teacher.grades.edit', $submission))
            ->assertOk()
            ->assertSee($student->user->name)
            ->assertSee('Simpan Nilai');
    }

    /**
     * @return array{0: Assignment, 1: User, 2: Teacher, 3: SchoolClass, 4: Subject, 5: Student}
     */
    private function makeGradingSetup(AssignmentType $type): array
    {
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
            'status' => AssignmentStatus::Published,
            'deadline' => now()->addDays(3),
        ]);

        return [$assignment, $teacherUser, $teacher, $class, $subject, $student];
    }
}
