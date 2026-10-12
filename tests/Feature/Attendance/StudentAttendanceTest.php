<?php

namespace Tests\Feature\Attendance;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceSessionStatus;
use App\Enums\AttendanceStatus;
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
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StudentAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_pending_state_when_no_session(): void
    {
        [$year, $class, $student, $studentUser] = $this->makeStudentInClass();

        $this->actingAs($studentUser)
            ->get(route('student.attendance.index'))
            ->assertOk()
            ->assertSee('Sesi absensi belum dibuka');
    }

    public function test_student_checks_in_and_is_marked_present(): void
    {
        $this->freezeClock('08:00');

        [$year, $class, $student, $studentUser] = $this->makeStudentInClass();
        $session = $this->openSession($class, $year, '09:00');

        $this->actingAs($studentUser)
            ->get(route('student.attendance.index'))
            ->assertOk()
            ->assertSee('Absen Sekarang');

        $this->actingAs($studentUser)
            ->post(route('student.attendance.check-in', $session))
            ->assertRedirect(route('student.attendance.index'));

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Hadir->value,
            'method' => AttendanceMethod::Button->value,
        ]);
    }

    public function test_student_checks_in_late_when_past_late_after(): void
    {
        $this->freezeClock('08:00');

        [$year, $class, $student, $studentUser] = $this->makeStudentInClass();
        $session = $this->openSession($class, $year, '07:00');

        $this->actingAs($studentUser)
            ->post(route('student.attendance.check-in', $session))
            ->assertRedirect(route('student.attendance.index'));

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Terlambat->value,
        ]);
    }

    public function test_student_cannot_check_in_twice(): void
    {
        $this->freezeClock('08:00');

        [$year, $class, $student, $studentUser] = $this->makeStudentInClass();
        $session = $this->openSession($class, $year, '09:00');

        AttendanceRecord::factory()->present()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($studentUser)
            ->post(route('student.attendance.check-in', $session))
            ->assertForbidden();
    }

    public function test_student_cannot_check_in_to_another_class_session(): void
    {
        $this->freezeClock('08:00');

        [$year, $class, $student, $studentUser] = $this->makeStudentInClass();

        $otherClass = SchoolClass::factory()->create(['school_year_id' => $year->id]);
        $session = AttendanceSession::factory()->create([
            'class_id' => $otherClass->id,
            'school_year_id' => $year->id,
            'date' => '2026-01-15',
            'status' => AttendanceSessionStatus::Open,
        ]);

        $this->actingAs($studentUser)
            ->post(route('student.attendance.check-in', $session))
            ->assertForbidden();
    }

    public function test_student_attendance_page_shows_recorded_status(): void
    {
        $this->freezeClock('08:00');

        [$year, $class, $student, $studentUser] = $this->makeStudentInClass();
        $session = $this->openSession($class, $year, '09:00');

        AttendanceRecord::factory()->present()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.attendance.index'))
            ->assertOk()
            ->assertSee('Anda sudah hadir');
    }

    public function test_non_student_cannot_access_student_attendance(): void
    {
        $teacherUser = User::factory()->asTeacher()->create();

        $this->actingAs($teacherUser)
            ->get(route('student.attendance.index'))
            ->assertForbidden();
    }

    /**
     * @return array{0: SchoolYear, 1: SchoolClass, 2: Student, 3: User}
     */
    private function makeStudentInClass(): array
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
        $student = Student::factory()->create(['user_id' => $studentUser->id, 'class_id' => $class->id]);

        return [$year, $class, $student, $studentUser];
    }

    private function openSession(SchoolClass $class, SchoolYear $year, string $lateAfter): AttendanceSession
    {
        return AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => '2026-01-15',
            'status' => AttendanceSessionStatus::Open,
            'late_after' => $lateAfter,
        ]);
    }

    private function freezeClock(string $time): void
    {
        $this->travelTo(Carbon::parse('2026-01-15 '.$time.':00'));
    }
}
