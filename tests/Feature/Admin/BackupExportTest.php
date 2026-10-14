<?php

namespace Tests\Feature\Admin;

use App\Enums\AttendanceSessionStatus;
use App\Enums\AttendanceStatus;
use App\Enums\PermitStatus;
use App\Enums\PermitType;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendancePermit;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AssignmentGradeBackupService;
use App\Services\AttendanceBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_backup_hub(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Backup Absensi')
            ->assertSee('Backup Tugas & Penilaian');
    }

    public function test_admin_can_download_the_attendance_backup_as_excel(): void
    {
        [$year, $class, $student] = $this->makeClassSetup();
        $this->record($this->makeSession($class, $year), $student, AttendanceStatus::Hadir);

        $admin = User::factory()->asAdmin()->create();

        $response = $this->actingAs($admin)->get(route('admin.backups.attendance', [
            'period' => 'monthly',
            'date' => now()->toDateString(),
            'format' => 'excel',
        ]));

        $response->assertOk();
        $response->assertDownload('backup-absensi-monthly-'.now()->startOfMonth()->format('Ymd').'.xlsx');
    }

    public function test_admin_can_download_the_attendance_backup_as_pdf(): void
    {
        [$year, $class, $student] = $this->makeClassSetup();
        $this->record($this->makeSession($class, $year), $student, AttendanceStatus::Hadir);

        $admin = User::factory()->asAdmin()->create();

        $response = $this->actingAs($admin)->get(route('admin.backups.attendance', [
            'period' => 'weekly',
            'date' => now()->toDateString(),
            'format' => 'pdf',
        ]));

        $response->assertOk();
        $response->assertDownload('backup-absensi-weekly-'.now()->startOfWeek()->format('Ymd').'.pdf');
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_admin_can_download_the_assignment_backup_as_excel_and_pdf(): void
    {
        [$year, $teacher, $class, $subject] = $this->makeTeachingSetup();

        Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'title' => 'Tugas Cerpen',
        ]);

        $admin = User::factory()->asAdmin()->create();
        $filename = 'backup-tugas-nilai-'.now()->format('Ymd');

        $this->actingAs($admin)
            ->get(route('admin.backups.assignments', ['format' => 'excel']))
            ->assertOk()
            ->assertDownload($filename.'.xlsx');

        $this->actingAs($admin)
            ->get(route('admin.backups.assignments', ['format' => 'pdf']))
            ->assertOk()
            ->assertDownload($filename.'.pdf');
    }

    public function test_backup_requires_a_supported_format(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.backups.attendance', ['period' => 'monthly']))
            ->assertSessionHasErrors('format');
    }

    public function test_non_admin_cannot_access_backups(): void
    {
        $teacher = User::factory()->asTeacher()->create();

        $this->actingAs($teacher)->get(route('admin.backups.index'))->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('admin.backups.attendance', ['period' => 'monthly', 'format' => 'excel']))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('admin.backups.assignments', ['format' => 'excel']))
            ->assertForbidden();
    }

    public function test_attendance_backup_includes_izin_and_sakit_permits(): void
    {
        [$year, $class, $student] = $this->makeClassSetup();
        $session = $this->makeSession($class, $year);
        $this->record($session, $student, AttendanceStatus::Izin);

        AttendancePermit::factory()->create([
            'student_id' => $student->id,
            'attendance_session_id' => $session->id,
            'date' => now()->toDateString(),
            'type' => PermitType::Izin,
            'reason' => 'Acara keluarga',
            'status' => PermitStatus::Approved,
        ]);
        AttendancePermit::factory()->create([
            'student_id' => $student->id,
            'attendance_session_id' => $session->id,
            'date' => now()->toDateString(),
            'type' => PermitType::Sakit,
            'reason' => 'Demam',
            'status' => PermitStatus::Approved,
        ]);

        $backup = app(AttendanceBackupService::class)->build([$class->id], 'monthly', now()->toDateString(), null);

        $this->assertSame(2, $backup['summary']['permits']);
        $this->assertSame(1, $backup['summary']['permits_izin']);
        $this->assertSame(1, $backup['summary']['permits_sakit']);
        $this->assertSame(1, $backup['summary']['izin']);
        $this->assertCount(2, $backup['permits']);
        $this->assertContains('Acara keluarga', $backup['permits']->pluck('reason')->all());
        $this->assertContains('Demam', $backup['permits']->pluck('reason')->all());
    }

    public function test_assignment_backup_summarises_tasks_and_grades(): void
    {
        [$year, $teacher, $class, $subject] = $this->makeTeachingSetup();
        [$student] = $this->makeStudents($class, 1);

        $assignment = Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'title' => 'Latihan Aljabar',
        ]);

        AssignmentSubmission::factory()->graded(90)->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'assignment_id' => $assignment->id,
            'school_year_id' => $year->id,
            'score' => 90,
        ]);

        $backup = app(AssignmentGradeBackupService::class)->build([$class->id], null, null, null, null);

        $this->assertSame(1, $backup['summary']['assignments']);
        $this->assertSame(1, $backup['summary']['submissions']);
        $this->assertSame(1, $backup['summary']['graded']);
        $this->assertSame(90.0, $backup['summary']['average']);
        $this->assertSame('Latihan Aljabar', $backup['assignmentRows']->first()['title']);
        $this->assertCount(1, $backup['grades']);
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

        return [$year, $teacher, $class, $subject];
    }

    /**
     * @return array{0: SchoolYear, 1: SchoolClass, 2: Student}
     */
    private function makeClassSetup(): array
    {
        $year = SchoolYear::factory()->active()->create(['name' => '2026/2027']);
        $class = SchoolClass::factory()->create(['school_year_id' => $year->id]);
        [$student] = $this->makeStudents($class, 1);

        return [$year, $class, $student];
    }

    /**
     * @return array<int, Student>
     */
    private function makeStudents(SchoolClass $class, int $count): array
    {
        return Student::factory()->count($count)->create(['class_id' => $class->id])->all();
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
