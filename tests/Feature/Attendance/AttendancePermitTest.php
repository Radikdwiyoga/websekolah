<?php

namespace Tests\Feature\Attendance;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Enums\PermitType;
use App\Models\AttendancePermit;
use App\Models\AttendanceSession;
use App\Models\Guardian;
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

class AttendancePermitTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_a_permit(): void
    {
        $context = $this->makeContext();

        $this->actingAs($context['studentUser'])
            ->from(route('student.permits.index'))
            ->post(route('student.permits.store'), [
                'date' => '2026-01-15',
                'type' => 'sakit',
                'reason' => 'Demam tinggi sejak pagi.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_permits', [
            'student_id' => $context['student']->id,
            'type' => PermitType::Sakit->value,
            'status' => 'pending',
        ]);
    }

    public function test_student_permit_requires_valid_data(): void
    {
        $context = $this->makeContext();

        $this->actingAs($context['studentUser'])
            ->from(route('student.permits.index'))
            ->post(route('student.permits.store'), [
                'date' => '',
                'type' => 'bogus',
                'reason' => '',
            ])
            ->assertSessionHasErrors(['date', 'type', 'reason']);

        $this->assertDatabaseCount('attendance_permits', 0);
    }

    public function test_student_submission_is_forced_to_their_own_student_record(): void
    {
        $context = $this->makeContext();

        $otherStudentUser = User::factory()->asStudent()->create();
        $otherStudent = Student::factory()->create([
            'user_id' => $otherStudentUser->id,
            'class_id' => $context['class']->id,
        ]);

        $this->actingAs($context['studentUser'])
            ->post(route('student.permits.store'), [
                'student_id' => $otherStudent->id,
                'date' => '2026-01-15',
                'type' => 'izin',
                'reason' => 'Keperluan keluarga.',
            ]);

        $this->assertDatabaseHas('attendance_permits', ['student_id' => $context['student']->id]);
        $this->assertDatabaseMissing('attendance_permits', ['student_id' => $otherStudent->id]);
    }

    public function test_student_index_only_lists_their_own_permits(): void
    {
        $context = $this->makeContext();

        AttendancePermit::factory()->create([
            'student_id' => $context['student']->id,
            'date' => '2026-01-15',
            'reason' => 'Keperluan pribadi saya.',
        ]);

        $otherStudentUser = User::factory()->asStudent()->create();
        $otherStudent = Student::factory()->create([
            'user_id' => $otherStudentUser->id,
            'class_id' => $context['class']->id,
        ]);
        AttendancePermit::factory()->create([
            'student_id' => $otherStudent->id,
            'date' => '2026-01-15',
            'reason' => 'Rahasia siswa lain.',
        ]);

        $this->actingAs($context['studentUser'])
            ->get(route('student.permits.index'))
            ->assertOk()
            ->assertSee('Keperluan pribadi saya.')
            ->assertDontSee('Rahasia siswa lain');
    }

    public function test_parent_can_submit_a_permit_for_a_linked_child(): void
    {
        $context = $this->makeParentContext();

        $this->actingAs($context['parentUser'])
            ->from(route('parent.permits.index'))
            ->post(route('parent.permits.store'), [
                'student_id' => $context['student']->id,
                'date' => '2026-01-15',
                'type' => 'izin',
                'reason' => 'Acara keluarga.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_permits', [
            'student_id' => $context['student']->id,
            'type' => PermitType::Izin->value,
        ]);
    }

    public function test_parent_cannot_submit_a_permit_for_an_unlinked_student(): void
    {
        $context = $this->makeContext();

        $parentUser = User::factory()->asParent()->create();
        Guardian::factory()->create(['user_id' => $parentUser->id]);

        $this->actingAs($parentUser)
            ->from(route('parent.permits.index'))
            ->post(route('parent.permits.store'), [
                'student_id' => $context['student']->id,
                'date' => '2026-01-15',
                'type' => 'izin',
                'reason' => 'Mencoba menembus data siswa lain.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('attendance_permits', 0);
    }

    public function test_student_can_attach_supporting_document(): void
    {
        Storage::fake('local');

        $context = $this->makeContext();

        $this->actingAs($context['studentUser'])
            ->post(route('student.permits.store'), [
                'date' => '2026-01-15',
                'type' => 'sakit',
                'reason' => 'Ada surat dokter.',
                'attachment' => UploadedFile::fake()->image('surat.jpg'),
            ])
            ->assertSessionHas('success');

        $permit = AttendancePermit::query()->firstOrFail();

        $this->assertNotNull($permit->attachment_path);
        Storage::disk('local')->assertExists($permit->attachment_path);
    }

    public function test_teacher_can_approve_a_permit_and_record_attendance(): void
    {
        $context = $this->makeContext();

        $session = AttendanceSession::factory()->create([
            'class_id' => $context['class']->id,
            'school_year_id' => $context['year']->id,
            'date' => '2026-01-15',
        ]);

        $permit = AttendancePermit::factory()->create([
            'student_id' => $context['student']->id,
            'date' => '2026-01-15',
            'type' => PermitType::Izin,
        ]);

        $this->actingAs($context['teacherUser'])
            ->post(route('teacher.permits.review', $permit), [
                'decision' => 'approved',
                'note' => 'Disetujui, semoga cepat sembuh.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_permits', [
            'id' => $permit->id,
            'status' => 'approved',
            'reviewed_by' => $context['teacherUser']->id,
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $context['student']->id,
            'status' => AttendanceStatus::Izin->value,
            'method' => AttendanceMethod::Manual->value,
        ]);
    }

    public function test_teacher_can_reject_a_permit_without_recording_attendance(): void
    {
        $context = $this->makeContext();

        $permit = AttendancePermit::factory()->create([
            'student_id' => $context['student']->id,
            'date' => '2026-01-15',
            'type' => PermitType::Izin,
        ]);

        $this->actingAs($context['teacherUser'])
            ->post(route('teacher.permits.review', $permit), [
                'decision' => 'rejected',
                'note' => 'Tanpa keterangan jelas.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_permits', [
            'id' => $permit->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseMissing('attendance_records', [
            'student_id' => $context['student']->id,
        ]);
    }

    public function test_teacher_cannot_review_a_permit_for_a_class_they_do_not_manage(): void
    {
        $context = $this->makeContext();

        $otherClass = SchoolClass::factory()->create(['school_year_id' => $context['year']->id]);
        $otherStudentUser = User::factory()->asStudent()->create();
        $otherStudent = Student::factory()->create([
            'user_id' => $otherStudentUser->id,
            'class_id' => $otherClass->id,
        ]);

        $permit = AttendancePermit::factory()->create([
            'student_id' => $otherStudent->id,
            'date' => '2026-01-15',
        ]);

        $this->actingAs($context['teacherUser'])
            ->post(route('teacher.permits.review', $permit), ['decision' => 'approved'])
            ->assertForbidden();

        $this->assertDatabaseHas('attendance_permits', [
            'id' => $permit->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_review_any_permit(): void
    {
        $context = $this->makeContext();

        $permit = AttendancePermit::factory()->create([
            'student_id' => $context['student']->id,
            'date' => '2026-01-15',
        ]);

        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->post(route('teacher.permits.review', $permit), ['decision' => 'approved'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_permits', [
            'id' => $permit->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_teacher_index_is_scoped_to_their_classes(): void
    {
        $context = $this->makeContext();

        $ownStudentUser = User::factory()->asStudent()->create(['name' => 'Siswa Kelas Sendiri']);
        $ownStudent = Student::factory()->create([
            'user_id' => $ownStudentUser->id,
            'class_id' => $context['class']->id,
        ]);
        AttendancePermit::factory()->create([
            'student_id' => $ownStudent->id,
            'date' => '2026-01-15',
        ]);

        $otherClass = SchoolClass::factory()->create(['school_year_id' => $context['year']->id]);
        $otherStudentUser = User::factory()->asStudent()->create(['name' => 'Siswa Kelas Lain']);
        $otherStudent = Student::factory()->create([
            'user_id' => $otherStudentUser->id,
            'class_id' => $otherClass->id,
        ]);
        AttendancePermit::factory()->create([
            'student_id' => $otherStudent->id,
            'date' => '2026-01-15',
        ]);

        $this->actingAs($context['teacherUser'])
            ->get(route('teacher.permits.index'))
            ->assertOk()
            ->assertSee('Siswa Kelas Sendiri')
            ->assertDontSee('Siswa Kelas Lain');
    }

    public function test_student_can_withdraw_their_own_pending_permit(): void
    {
        $context = $this->makeContext();

        $permit = AttendancePermit::factory()->create([
            'student_id' => $context['student']->id,
            'date' => '2026-01-15',
        ]);

        $this->actingAs($context['studentUser'])
            ->delete(route('student.permits.destroy', $permit))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('attendance_permits', ['id' => $permit->id]);
    }

    public function test_student_cannot_delete_an_approved_permit(): void
    {
        $context = $this->makeContext();

        $permit = AttendancePermit::factory()->approved()->create([
            'student_id' => $context['student']->id,
            'date' => '2026-01-15',
        ]);

        $this->actingAs($context['studentUser'])
            ->delete(route('student.permits.destroy', $permit))
            ->assertForbidden();

        $this->assertDatabaseHas('attendance_permits', ['id' => $permit->id]);
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

    /**
     * @return array{year: SchoolYear, class: SchoolClass, student: Student, studentUser: User, parentUser: User, guardian: Guardian}
     */
    private function makeParentContext(): array
    {
        $context = $this->makeContext();

        $parentUser = User::factory()->asParent()->create();
        $guardian = Guardian::factory()->create(['user_id' => $parentUser->id]);
        $guardian->students()->attach($context['student']->id, ['relation' => 'Ibu', 'is_primary' => true]);

        return $context + compact('parentUser', 'guardian');
    }
}
