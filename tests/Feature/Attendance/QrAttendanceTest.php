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
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class QrAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_page_shows_permanent_qr_card_even_without_session(): void
    {
        $context = $this->makeContext();

        $this->actingAs($context['studentUser'])
            ->get(route('student.attendance.index'))
            ->assertOk()
            ->assertSee('Kartu QR Absensi')
            ->assertSee($context['student']->nisn)
            ->assertSee($context['class']->name);
    }

    public function test_qr_payload_round_trips_and_is_signed(): void
    {
        $context = $this->makeContext();
        $service = app(QrCodeService::class);

        $payload = $service->encode($context['student']);
        $decoded = $service->decode($payload);

        $this->assertIsArray($decoded);
        $this->assertSame($context['student']->id, $decoded['student_id']);
        $this->assertSame((string) $context['student']->nisn, $decoded['nisn']);
        $this->assertSame((string) $context['student']->nis, $decoded['nis']);
        $this->assertSame($context['class']->name, $decoded['class']);
        $this->assertArrayHasKey('sig', $decoded);
    }

    public function test_tampered_qr_payload_is_rejected(): void
    {
        $context = $this->makeContext();
        $service = app(QrCodeService::class);

        $data = $service->payload($context['student']);
        $data['name'] = 'Penyusup';

        $this->assertNull($service->decode((string) json_encode($data)));
    }

    public function test_student_can_check_in_with_their_own_qr_code(): void
    {
        $this->freezeClock('08:00');

        $context = $this->makeContext();
        $session = $this->openSession($context['class'], $context['year'], '09:00');

        $payload = app(QrCodeService::class)->encode($context['student']);

        $this->actingAs($context['studentUser'])
            ->from(route('student.attendance.index'))
            ->post(route('student.attendance.qr.check-in'), ['qr_payload' => $payload])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $context['student']->id,
            'status' => AttendanceStatus::Hadir->value,
            'method' => AttendanceMethod::Qr->value,
        ]);
    }

    public function test_student_cannot_check_in_with_another_students_qr_code(): void
    {
        $this->freezeClock('08:00');

        $context = $this->makeContext();
        $this->openSession($context['class'], $context['year'], '09:00');

        $otherUser = User::factory()->asStudent()->create();
        Student::factory()->create(['user_id' => $otherUser->id, 'class_id' => $context['class']->id]);

        $payload = app(QrCodeService::class)->encode($context['student']);

        $this->actingAs($otherUser)
            ->from(route('student.attendance.index'))
            ->post(route('student.attendance.qr.check-in'), ['qr_payload' => $payload])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('attendance_records', [
            'student_id' => $context['student']->id,
        ]);
    }

    public function test_teacher_can_scan_student_qr_to_mark_attendance(): void
    {
        $this->freezeClock('08:00');

        $context = $this->makeContext();
        $session = $this->openSession($context['class'], $context['year'], '09:00');

        $payload = app(QrCodeService::class)->encode($context['student']);

        $this->actingAs($context['teacherUser'])
            ->from(route('teacher.attendance.show', $context['class']))
            ->post(route('teacher.attendance.scan-qr', $session), ['qr_payload' => $payload])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $context['student']->id,
            'method' => AttendanceMethod::Qr->value,
        ]);
    }

    public function test_teacher_cannot_scan_qr_for_a_class_they_do_not_manage(): void
    {
        $context = $this->makeContext();

        $otherClass = SchoolClass::factory()->create(['school_year_id' => $context['year']->id]);
        $otherStudentUser = User::factory()->asStudent()->create();
        $otherStudent = Student::factory()->create([
            'user_id' => $otherStudentUser->id,
            'class_id' => $otherClass->id,
        ]);

        $session = AttendanceSession::factory()->create([
            'class_id' => $otherClass->id,
            'school_year_id' => $context['year']->id,
            'date' => now()->toDateString(),
            'status' => AttendanceSessionStatus::Open,
        ]);

        $payload = app(QrCodeService::class)->encode($otherStudent);

        $this->actingAs($context['teacherUser'])
            ->post(route('teacher.attendance.scan-qr', $session), ['qr_payload' => $payload])
            ->assertForbidden();
    }

    public function test_duplicate_qr_check_in_is_rejected(): void
    {
        $this->freezeClock('08:00');

        $context = $this->makeContext();
        $session = $this->openSession($context['class'], $context['year'], '09:00');

        AttendanceRecord::factory()->present()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $context['student']->id,
        ]);

        $payload = app(QrCodeService::class)->encode($context['student']);

        $this->actingAs($context['studentUser'])
            ->from(route('student.attendance.index'))
            ->post(route('student.attendance.qr.check-in'), ['qr_payload' => $payload])
            ->assertSessionHas('error');

        $this->assertSame(1, $session->records()->count());
    }

    public function test_student_can_download_their_qr_image(): void
    {
        $context = $this->makeContext();

        $response = $this->actingAs($context['studentUser'])
            ->get(route('student.attendance.qr.image'));

        $response->assertOk();
        $this->assertStringContainsString('image/svg+xml', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    /**
     * @return array{year: SchoolYear, teacherUser: User, teacher: Teacher, class: SchoolClass, student: Student, studentUser: User}
     */
    private function makeContext(): array
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

        return compact('year', 'teacherUser', 'teacher', 'class', 'student', 'studentUser');
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
