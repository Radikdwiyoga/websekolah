<?php

namespace Tests\Feature\Attendance;

use App\Enums\AttendanceSessionStatus;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRecapTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_recap_counts_records_per_student(): void
    {
        [$year, $teacherUser, $teacher, $class] = $this->makeTeacherSetup();
        [$present, $late] = $this->makeStudents($class, 2);

        $session = $this->makeSession($class, $year);
        $this->record($session, $present, AttendanceStatus::Hadir);
        $this->record($session, $late, AttendanceStatus::Terlambat);

        $this->actingAs($teacherUser)
            ->get(route('teacher.attendance.recap'))
            ->assertOk()
            ->assertSee($present->user->name)
            ->assertSee($late->user->name)
            ->assertViewHas('recap', function (array $recap) use ($present, $late): bool {
                $rows = $recap['rows']->keyBy(fn (array $row) => $row['student']->id);

                return $recap['summary']['hadir'] === 1
                    && $recap['summary']['terlambat'] === 1
                    && $rows[$present->id]['percentage'] === 100
                    && $rows[$late->id]['percentage'] === 100;
            });
    }

    public function test_teacher_recap_ignores_classes_they_do_not_teach(): void
    {
        [$year, $teacherUser, $teacher, $class] = $this->makeTeacherSetup();

        $foreignClass = SchoolClass::factory()->create(['school_year_id' => $year->id]);
        $foreignStudent = Student::factory()->create(['class_id' => $foreignClass->id]);
        $foreignSession = $this->makeSession($foreignClass, $year);
        $this->record($foreignSession, $foreignStudent, AttendanceStatus::Hadir);

        $this->actingAs($teacherUser)
            ->get(route('teacher.attendance.recap', ['class_id' => $foreignClass->id]))
            ->assertOk()
            ->assertDontSee($foreignStudent->user->name)
            ->assertViewHas('recap', fn (array $recap): bool => $recap['selectedClassId'] === null
                && $recap['classes']->doesntContain('id', $foreignClass->id));
    }

    public function test_admin_recap_covers_every_class_in_the_active_year(): void
    {
        [$year, , , $class] = $this->makeTeacherSetup();
        $admin = User::factory()->asSuperAdmin()->create();

        $classA = SchoolClass::factory()->create(['school_year_id' => $year->id]);
        $classB = SchoolClass::factory()->create(['school_year_id' => $year->id]);
        $studentA = Student::factory()->create(['class_id' => $classA->id]);
        $studentB = Student::factory()->create(['class_id' => $classB->id]);

        foreach ([[$classA, $studentA], [$classB, $studentB]] as [$studentClass, $student]) {
            $this->record($this->makeSession($studentClass, $year), $student, AttendanceStatus::Hadir);
        }

        $this->actingAs($admin)
            ->get(route('admin.attendance.recap'))
            ->assertOk()
            ->assertSee($studentA->user->name)
            ->assertSee($studentB->user->name)
            ->assertViewHas('recap', fn (array $recap): bool => $recap['summary']['siswa'] === 2);
    }

    public function test_status_filter_keeps_only_matching_students(): void
    {
        [$year, $teacherUser, $teacher, $class] = $this->makeTeacherSetup();
        [$present, $absent] = $this->makeStudents($class, 2);

        $session = $this->makeSession($class, $year);
        $this->record($session, $present, AttendanceStatus::Hadir);
        $this->record($session, $absent, AttendanceStatus::Alfa);

        $this->actingAs($teacherUser)
            ->get(route('teacher.attendance.recap', ['status' => AttendanceStatus::Alfa->value]))
            ->assertOk()
            ->assertSee($absent->user->name)
            ->assertDontSee($present->user->name)
            ->assertViewHas('recap', fn (array $recap): bool => $recap['rows']->count() === 1
                && $recap['rows']->first()['student']->id === $absent->id);
    }

    public function test_unlisted_status_filter_is_ignored(): void
    {
        [$year, $teacherUser, $teacher, $class] = $this->makeTeacherSetup();
        [$student] = $this->makeStudents($class, 1);
        $this->record($this->makeSession($class, $year), $student, AttendanceStatus::Hadir);

        $this->actingAs($teacherUser)
            ->get(route('teacher.attendance.recap', ['status' => AttendanceStatus::BelumAbsen->value]))
            ->assertOk()
            ->assertViewHas('recap', fn (array $recap): bool => $recap['status'] === null
                && $recap['rows']->count() === 1);
    }

    public function test_recap_can_be_downloaded_as_csv(): void
    {
        [$year, $teacherUser, $teacher, $class] = $this->makeTeacherSetup();
        [$student] = $this->makeStudents($class, 1);
        $session = $this->makeSession($class, $year);
        $this->record($session, $student, AttendanceStatus::Hadir);

        $response = $this->actingAs($teacherUser)
            ->get(route('teacher.attendance.recap.export'));

        $response->assertOk();
        $response->assertDownload('rekap-absensi-'.now()->format('Y-m').'.csv');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('No,Nama,Kelas,Hadir,Terlambat,Izin,Sakit,Alfa,Total', $csv);
        $this->assertStringContainsString($student->user->name, $csv);
    }

    public function test_student_cannot_view_teacher_recap(): void
    {
        $studentUser = User::factory()->asStudent()->create();

        $this->actingAs($studentUser)
            ->get(route('teacher.attendance.recap'))
            ->assertForbidden();
    }

    public function test_student_nilai_page_lists_grades_and_average(): void
    {
        $student = Student::factory()->create();
        $matematika = Subject::factory()->create(['name' => 'Matematika']);
        $fisika = Subject::factory()->create(['name' => 'Fisika']);

        Grade::factory()->create(['student_id' => $student->id, 'subject_id' => $matematika->id, 'score' => 80]);
        Grade::factory()->create(['student_id' => $student->id, 'subject_id' => $fisika->id, 'score' => 90]);

        $this->actingAs($student->user)
            ->get(route('student.grades.index'))
            ->assertOk()
            ->assertSee('Matematika')
            ->assertSee('Fisika')
            ->assertViewHas('average', fn (?int $average): bool => $average === 85)
            ->assertViewHas('bySubject', fn ($bySubject): bool => $bySubject->count() === 2);
    }

    public function test_student_nilai_page_shows_empty_state_without_grades(): void
    {
        $student = Student::factory()->create();

        $this->actingAs($student->user)
            ->get(route('student.grades.index'))
            ->assertOk()
            ->assertSee('Belum ada nilai')
            ->assertViewHas('average', null);
    }

    /**
     * @return array{0: SchoolYear, 1: User, 2: Teacher, 3: SchoolClass}
     */
    private function makeTeacherSetup(): array
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

        return [$year, $teacherUser, $teacher, $class];
    }

    /**
     * @return array<int, Student>
     */
    private function makeStudents(SchoolClass $class, int $count): array
    {
        return Student::factory()
            ->count($count)
            ->create(['class_id' => $class->id])
            ->all();
    }

    private function makeSession(SchoolClass $class, SchoolYear $year): AttendanceSession
    {
        return AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => now()->toDateString(),
            'status' => AttendanceSessionStatus::Closed,
        ]);
    }

    private function record(AttendanceSession $session, Student $student, AttendanceStatus $status): AttendanceRecord
    {
        return AttendanceRecord::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => $status,
        ]);
    }
}
