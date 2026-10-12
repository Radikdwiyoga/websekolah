<?php

namespace Tests\Feature\Assignment;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
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
use Tests\TestCase;

class TeacherAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_index_lists_own_assignments(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $assignment = Assignment::factory()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'title' => 'Latihan Aljabar',
        ]);

        // Another teacher's assignment must not leak into the list.
        Assignment::factory()->create(['title' => 'Tugas Guru Lain']);

        $this->actingAs($teacherUser)
            ->get(route('teacher.assignments.index'))
            ->assertOk()
            ->assertSee('Latihan Aljabar')
            ->assertDontSee('Tugas Guru Lain');
    }

    public function test_teacher_can_create_a_draft_assignment(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $this->actingAs($teacherUser)
            ->post(route('teacher.assignments.store'), [
                'title' => 'Tugas Baru',
                'pair' => $class->id.':'.$subject->id,
                'type' => AssignmentType::MultipleChoice->value,
                'description' => 'Kerjakan dengan teliti.',
                'deadline' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'grade_weight' => 100,
                'late_deduction_percent' => 10,
                'allow_resubmit' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assignments', [
            'title' => 'Tugas Baru',
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'status' => AssignmentStatus::Draft->value,
        ]);
    }

    public function test_teacher_cannot_create_for_a_class_they_do_not_teach(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $otherClass = SchoolClass::factory()->create(['school_year_id' => $year->id]);

        $this->actingAs($teacherUser)
            ->post(route('teacher.assignments.store'), [
                'title' => 'Tugas Ilegal',
                'pair' => $otherClass->id.':'.$subject->id,
                'type' => AssignmentType::MultipleChoice->value,
                'grade_weight' => 100,
                'late_deduction_percent' => 0,
            ])
            ->assertForbidden();
    }

    public function test_teacher_cannot_view_another_teachers_assignment(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $assignment = Assignment::factory()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
        ]);

        $this->actingAs(User::factory()->asSuperAdmin()->create())
            ->get(route('teacher.assignments.show', $assignment))
            ->assertOk();

        $otherTeacherUser = User::factory()->asTeacher()->create();
        Teacher::factory()->create(['user_id' => $otherTeacherUser->id]);

        $this->actingAs($otherTeacherUser)
            ->get(route('teacher.assignments.show', $assignment))
            ->assertForbidden();
    }

    public function test_teacher_can_add_multiple_choice_questions(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $assignment = $this->makeDraftAssignment($teacher, $class, $subject, $year, AssignmentType::MultipleChoice);

        $this->actingAs($teacherUser)
            ->post(route('teacher.assignments.questions.store', $assignment), [
                'questions' => [
                    [
                        'question_text' => 'Berapa hasil 1 + 1?',
                        'points' => 50,
                        'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'],
                        'correct_answer' => 'B',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assignment_questions', [
            'assignment_id' => $assignment->id,
            'question_text' => 'Berapa hasil 1 + 1?',
            'type' => AssignmentType::MultipleChoice->value,
        ]);
    }

    public function test_publish_requires_questions_for_question_based_types(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $assignment = $this->makeDraftAssignment($teacher, $class, $subject, $year, AssignmentType::MultipleChoice);

        $this->actingAs($teacherUser)
            ->post(route('teacher.assignments.publish', $assignment))
            ->assertSessionHas('error');

        $this->assertSame(AssignmentStatus::Draft, $assignment->fresh()->status);
    }

    public function test_teacher_can_publish_after_adding_questions(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $assignment = $this->makeDraftAssignment($teacher, $class, $subject, $year, AssignmentType::Essay);

        $this->actingAs($teacherUser)->post(route('teacher.assignments.questions.store', $assignment), [
            'questions' => [
                ['question_text' => 'Jelaskan konsepnya.', 'points' => 100],
            ],
        ])->assertRedirect();

        $this->actingAs($teacherUser)
            ->post(route('teacher.assignments.publish', $assignment))
            ->assertSessionHas('success');

        $this->assertSame(AssignmentStatus::Published, $assignment->fresh()->status);
    }

    public function test_file_upload_assignment_can_be_published_without_questions(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $assignment = $this->makeDraftAssignment($teacher, $class, $subject, $year, AssignmentType::FileUpload);

        $this->actingAs($teacherUser)
            ->post(route('teacher.assignments.publish', $assignment))
            ->assertSessionHas('success');

        $this->assertSame(AssignmentStatus::Published, $assignment->fresh()->status);
    }

    public function test_published_assignment_cannot_be_edited(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $assignment = Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
        ]);

        $this->actingAs($teacherUser)
            ->get(route('teacher.assignments.edit', $assignment))
            ->assertRedirect(route('teacher.assignments.show', $assignment))
            ->assertSessionHas('error');
    }

    public function test_teacher_can_close_and_delete_drafts(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $published = Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
        ]);

        $this->actingAs($teacherUser)
            ->post(route('teacher.assignments.close', $published))
            ->assertSessionHas('success');

        $this->assertSame(AssignmentStatus::Closed, $published->fresh()->status);

        $draft = $this->makeDraftAssignment($teacher, $class, $subject, $year, AssignmentType::Essay);

        $this->actingAs($teacherUser)
            ->delete(route('teacher.assignments.destroy', $draft))
            ->assertRedirect(route('teacher.assignments.index'));

        $this->assertSoftDeleted('assignments', ['id' => $draft->id]);
    }

    public function test_teacher_can_view_a_published_assignment_with_submissions(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $student = Student::factory()->create([
            'user_id' => User::factory()->asStudent()->create()->id,
            'class_id' => $class->id,
        ]);

        $assignment = Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'type' => AssignmentType::Essay,
        ]);

        AssignmentQuestion::factory()->essay()->create([
            'assignment_id' => $assignment->id,
            'order' => 1,
        ]);

        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($teacherUser)
            ->get(route('teacher.assignments.show', $assignment))
            ->assertOk()
            ->assertSee($assignment->title)
            ->assertSee($student->user->name);
    }

    /**
     * @return array{0: SchoolYear, 1: User, 2: Teacher, 3: SchoolClass, 4: Subject}
     */
    private function makeTeachingSetup(): array
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

        return [$year, $teacherUser, $teacher, $class, $subject];
    }

    private function makeDraftAssignment(
        Teacher $teacher,
        SchoolClass $class,
        Subject $subject,
        SchoolYear $year,
        AssignmentType $type,
    ): Assignment {
        return Assignment::factory()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'type' => $type,
            'status' => AssignmentStatus::Draft,
        ]);
    }
}
