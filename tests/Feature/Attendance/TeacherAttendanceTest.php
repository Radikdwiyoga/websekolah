<?php

namespace Tests\Feature\Attendance;

use App\Enums\AttendanceSessionStatus;
use App\Enums\AttendanceStatus;
use App\Enums\SettingType;
use App\Models\ApplicationSetting;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_index_lists_taught_classes(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $this->actingAs($teacherUser)
            ->get(route('teacher.attendance.index'))
            ->assertOk()
            ->assertSee($class->name);
    }

    public function test_teacher_cannot_access_class_of_another_teacher(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $otherUser = User::factory()->asTeacher()->create();
        Teacher::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($otherUser)
            ->get(route('teacher.attendance.show', $class))
            ->assertForbidden();
    }

    public function test_homeroom_teacher_can_access_their_class(): void
    {
        $year = SchoolYear::factory()->active()->create(['name' => '2026/2027']);
        $teacherUser = User::factory()->asTeacher()->create();
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
        $class = SchoolClass::factory()->create([
            'school_year_id' => $year->id,
            'homeroom_teacher_id' => $teacher->id,
        ]);

        $this->actingAs($teacherUser)
            ->get(route('teacher.attendance.show', $class))
            ->assertOk();
    }

    public function test_teacher_opens_session_using_settings_window(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        ApplicationSetting::factory()->create(['key' => 'attendance_open_time', 'value' => '08:00', 'type' => SettingType::Text, 'group' => 'attendance']);
        ApplicationSetting::factory()->create(['key' => 'attendance_close_time', 'value' => '10:00', 'type' => SettingType::Text, 'group' => 'attendance']);
        ApplicationSetting::factory()->create(['key' => 'attendance_late_after', 'value' => '08:15', 'type' => SettingType::Text, 'group' => 'attendance']);

        $this->actingAs($teacherUser)
            ->post(route('teacher.attendance.open', $class))
            ->assertRedirect(route('teacher.attendance.show', $class));

        $this->actingAs($teacherUser)
            ->get(route('teacher.attendance.show', $class))
            ->assertOk()
            ->assertSee('Daftar Kehadiran Siswa');

        $this->assertDatabaseHas('attendance_sessions', [
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'status' => AttendanceSessionStatus::Open->value,
            'open_at' => '08:00',
            'close_at' => '10:00',
            'late_after' => '08:15',
            'opened_by' => $teacherUser->id,
        ]);
    }

    public function test_opening_twice_for_same_day_creates_only_one_session(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => now()->toDateString(),
            'status' => AttendanceSessionStatus::Open,
        ]);

        $this->actingAs($teacherUser)
            ->post(route('teacher.attendance.open', $class))
            ->assertRedirect(route('teacher.attendance.show', $class));

        $this->assertSame(
            1,
            AttendanceSession::query()->where('class_id', $class->id)->whereDate('date', now()->toDateString())->count(),
        );
    }

    public function test_teacher_cannot_reopen_a_closed_session(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();

        AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => now()->toDateString(),
            'status' => AttendanceSessionStatus::Closed,
            'closed_at' => now(),
        ]);

        $this->actingAs($teacherUser)
            ->post(route('teacher.attendance.open', $class))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_close_marks_unattended_students_as_alfa_and_logs_changes(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();
        $studentUser = User::factory()->asStudent()->create();
        $student = Student::factory()->create(['user_id' => $studentUser->id, 'class_id' => $class->id]);

        $session = AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => now()->toDateString(),
            'status' => AttendanceSessionStatus::Open,
        ]);

        $record = AttendanceRecord::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::BelumAbsen,
        ]);

        $this->actingAs($teacherUser)
            ->post(route('teacher.attendance.close', $session))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_records', [
            'id' => $record->id,
            'status' => AttendanceStatus::Alfa->value,
            'updated_by' => $teacherUser->id,
        ]);

        $this->assertDatabaseHas('attendance_sessions', [
            'id' => $session->id,
            'status' => AttendanceSessionStatus::Closed->value,
            'closed_by' => $teacherUser->id,
        ]);

        $this->assertDatabaseHas('attendance_status_changes', [
            'attendance_record_id' => $record->id,
            'from_status' => AttendanceStatus::BelumAbsen->value,
            'to_status' => AttendanceStatus::Alfa->value,
            'changed_by' => $teacherUser->id,
        ]);
    }

    public function test_update_records_creates_missing_and_revises_existing_with_audit(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();
        $studentA = Student::factory()->create([
            'user_id' => User::factory()->asStudent()->create()->id,
            'class_id' => $class->id,
        ]);
        $studentB = Student::factory()->create([
            'user_id' => User::factory()->asStudent()->create()->id,
            'class_id' => $class->id,
        ]);

        $session = AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => now()->toDateString(),
            'status' => AttendanceSessionStatus::Open,
        ]);

        $this->actingAs($teacherUser)->post(route('teacher.attendance.update', $session), [
            'status' => [
                $studentA->id => AttendanceStatus::Hadir->value,
                $studentB->id => AttendanceStatus::Izin->value,
            ],
            'note' => [
                $studentB->id => 'Izin keluarga',
            ],
        ])->assertRedirect()->assertSessionHas('success');

        $recordA = AttendanceRecord::query()
            ->where('attendance_session_id', $session->id)
            ->where('student_id', $studentA->id)
            ->firstOrFail();
        $recordB = AttendanceRecord::query()
            ->where('attendance_session_id', $session->id)
            ->where('student_id', $studentB->id)
            ->firstOrFail();

        $this->assertSame(AttendanceStatus::Hadir, $recordA->status);
        $this->assertSame(AttendanceStatus::Izin, $recordB->status);
        $this->assertSame('Izin keluarga', $recordB->note);

        // Revise A → Sakit, expecting a fresh audit entry for the change.
        $this->actingAs($teacherUser)->post(route('teacher.attendance.update', $session), [
            'status' => [
                $studentA->id => AttendanceStatus::Sakit->value,
                $studentB->id => AttendanceStatus::Izin->value,
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('attendance_records', [
            'id' => $recordA->id,
            'status' => AttendanceStatus::Sakit->value,
            'updated_by' => $teacherUser->id,
        ]);

        $this->assertDatabaseHas('attendance_status_changes', [
            'attendance_record_id' => $recordA->id,
            'from_status' => AttendanceStatus::Hadir->value,
            'to_status' => AttendanceStatus::Sakit->value,
            'changed_by' => $teacherUser->id,
        ]);
    }

    public function test_update_rejects_invalid_status_value(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();
        $student = Student::factory()->create([
            'user_id' => User::factory()->asStudent()->create()->id,
            'class_id' => $class->id,
        ]);

        $session = AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => now()->toDateString(),
            'status' => AttendanceSessionStatus::Open,
        ]);

        $this->actingAs($teacherUser)
            ->post(route('teacher.attendance.update', $session), [
                'status' => [$student->id => 'hadir-oke'],
            ])
            ->assertSessionHasErrors('status.'.$student->id);
    }

    public function test_admin_can_manage_class_attendance(): void
    {
        [$year, $teacherUser, $teacher, $class, $subject] = $this->makeTeachingSetup();
        $admin = User::factory()->asSuperAdmin()->create();

        $this->actingAs($admin)
            ->get(route('teacher.attendance.show', $class))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('teacher.attendance.open', $class))
            ->assertRedirect(route('teacher.attendance.show', $class));

        $this->assertDatabaseHas('attendance_sessions', [
            'class_id' => $class->id,
            'status' => AttendanceSessionStatus::Open->value,
            'opened_by' => $admin->id,
        ]);
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
}
