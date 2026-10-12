<?php

namespace Tests\Feature\Parent;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_attendance_page_lists_child_history(): void
    {
        [$year, $teacher, $class] = $this->makeTeachingSetup();
        [$parentUser, $guardian] = $this->makeParent();
        $child = $this->makeChild($guardian, $class, 'Budi Santoso');

        $present = AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => today()->subDay()->toDateString(),
        ]);
        AttendanceRecord::factory()->present()->create([
            'attendance_session_id' => $present->id,
            'student_id' => $child->id,
        ]);

        $late = AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => today()->subDays(2)->toDateString(),
        ]);
        AttendanceRecord::factory()->late()->create([
            'attendance_session_id' => $late->id,
            'student_id' => $child->id,
        ]);

        $this->actingAs($parentUser)
            ->get(route('parent.attendance.index', ['student' => $child->id]))
            ->assertOk()
            ->assertSee('Kehadiran Anak')
            ->assertSee('Budi Santoso')
            ->assertSee('Hadir')
            ->assertSee('Terlambat')
            ->assertSee('2 sesi');
    }

    public function test_parent_assignments_page_shows_status_and_score(): void
    {
        [$year, $teacher, $class, $subject] = $this->makeTeachingSetup();
        [$parentUser, $guardian] = $this->makeParent();
        $child = $this->makeChild($guardian, $class, 'Budi Santoso');

        $graded = Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'deadline' => now()->addDays(2),
            'title' => 'Tugas Sudah Dinilai',
        ]);
        AssignmentSubmission::factory()->graded(88)->create([
            'assignment_id' => $graded->id,
            'student_id' => $child->id,
        ]);

        Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'deadline' => now()->addDays(3),
            'title' => 'Tugas Belum Dikerjakan',
        ]);

        $this->actingAs($parentUser)
            ->get(route('parent.assignments.index', ['student' => $child->id]))
            ->assertOk()
            ->assertSee('Tugas Anak')
            ->assertSee('Tugas Sudah Dinilai')
            ->assertSee('Nilai 88')
            ->assertSee('Selesai')
            ->assertSee('Tugas Belum Dikerjakan')
            ->assertSee('Belum Dikerjakan');
    }

    public function test_parent_grades_page_shows_scores_and_average(): void
    {
        [$year, $teacher, $class, $subject] = $this->makeTeachingSetup();
        [$parentUser, $guardian] = $this->makeParent();
        $child = $this->makeChild($guardian, $class, 'Budi Santoso');

        Grade::factory()->create(['student_id' => $child->id, 'subject_id' => $subject->id, 'score' => 90]);
        Grade::factory()->create(['student_id' => $child->id, 'subject_id' => $subject->id, 'score' => 80]);

        $this->actingAs($parentUser)
            ->get(route('parent.grades.index', ['student' => $child->id]))
            ->assertOk()
            ->assertSee('Nilai Anak')
            ->assertSee('Matematika')
            ->assertSee('90')
            ->assertSee('80')
            ->assertSee('Nilai Tertinggi')
            ->assertSee('85');
    }

    public function test_parent_pages_ignore_a_child_that_is_not_linked(): void
    {
        [$year, $teacher, $class, $subject] = $this->makeTeachingSetup();
        $otherClass = SchoolClass::factory()->create(['school_year_id' => $year->id]);

        $foreignUser = User::factory()->asStudent()->create(['name' => 'Siswa Lain']);
        $foreign = Student::factory()->create(['user_id' => $foreignUser->id, 'class_id' => $otherClass->id]);
        Grade::factory()->create(['student_id' => $foreign->id, 'subject_id' => $subject->id, 'score' => 99]);

        [$parentUser, $guardian] = $this->makeParent();
        $this->makeChild($guardian, $class, 'Anak Terhubung');

        $this->actingAs($parentUser)
            ->get(route('parent.grades.index', ['student' => $foreign->id]))
            ->assertOk()
            ->assertSee('Anak Terhubung')
            ->assertDontSee('Siswa Lain');
    }

    public function test_parent_without_linked_children_sees_empty_state(): void
    {
        $parentUser = User::factory()->asParent()->create();
        Guardian::factory()->create(['user_id' => $parentUser->id]);

        $this->actingAs($parentUser)
            ->get(route('parent.attendance.index'))
            ->assertOk()
            ->assertSee('Belum ada anak terhubung');
    }

    public function test_non_parents_cannot_access_parent_pages(): void
    {
        $this->get(route('parent.attendance.index'))->assertRedirect(route('login'));

        $student = User::factory()->asStudent()->create();
        $this->actingAs($student)->get(route('parent.attendance.index'))->assertForbidden();
        $this->actingAs($student)->get(route('parent.assignments.index'))->assertForbidden();
        $this->actingAs($student)->get(route('parent.grades.index'))->assertForbidden();

        $teacher = User::factory()->asTeacher()->create();
        $this->actingAs($teacher)->get(route('parent.attendance.index'))->assertForbidden();

        $admin = User::factory()->asSuperAdmin()->create();
        $this->actingAs($admin)->get(route('parent.grades.index'))->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Guardian}
     */
    private function makeParent(): array
    {
        $parentUser = User::factory()->asParent()->create();
        $guardian = Guardian::factory()->create(['user_id' => $parentUser->id]);

        return [$parentUser, $guardian];
    }

    private function makeChild(Guardian $guardian, SchoolClass $class, string $name): Student
    {
        $childUser = User::factory()->asStudent()->create(['name' => $name]);
        $child = Student::factory()->create(['user_id' => $childUser->id, 'class_id' => $class->id]);

        $guardian->students()->attach($child->id, ['relation' => 'Ibu', 'is_primary' => true]);

        return $child;
    }

    /**
     * @return array{0: SchoolYear, 1: Teacher, 2: SchoolClass, 3: Subject}
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

        return [$year, $teacher, $class, $subject];
    }
}
