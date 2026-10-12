<?php

namespace Tests\Feature\Dashboard;

use App\Enums\AttendanceStatus;
use App\Enums\SubmissionStatus;
use App\Enums\SystemLogStatus;
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
use App\Models\SystemLog;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_shows_attendance_tasks_and_average_grade(): void
    {
        [$year, $teacher, $class, $subject] = $this->makeTeachingSetup();
        $studentUser = User::factory()->asStudent()->create();
        $student = Student::factory()->create(['user_id' => $studentUser->id, 'class_id' => $class->id]);

        $session = AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => today()->toDateString(),
        ]);
        AttendanceRecord::factory()->present()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
        ]);

        $assignment = Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'deadline' => now()->addDays(3),
            'title' => 'Tugas Matematika Pecahan',
        ]);
        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
        ]);
        Grade::factory()->create(['student_id' => $student->id, 'subject_id' => $subject->id, 'score' => 88]);

        $this->actingAs($studentUser)
            ->get(route('dashboard.student'))
            ->assertOk()
            ->assertSee('Absensi Hari Ini')
            ->assertSee('Hadir')
            ->assertSee('Tugas Terbaru')
            ->assertSee('Tugas Matematika Pecahan')
            ->assertSee('Selesai')
            ->assertSee('88');
    }

    public function test_student_without_today_attendance_sees_pending_state(): void
    {
        [$year, $teacher, $class, $subject] = $this->makeTeachingSetup();
        $studentUser = User::factory()->asStudent()->create();
        Student::factory()->create(['user_id' => $studentUser->id, 'class_id' => $class->id]);

        Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'deadline' => now()->addDays(3),
            'title' => 'Tugas IPA Ekosistem',
        ]);

        $this->actingAs($studentUser)
            ->get(route('dashboard.student'))
            ->assertOk()
            ->assertSee('Belum absen hari ini')
            ->assertSee('Tugas IPA Ekosistem')
            ->assertSee('Belum Dikerjakan');
    }

    public function test_teacher_dashboard_shows_kpis_assigned_classes_and_ungraded_assignments(): void
    {
        [$year, $teacher, $class, $subject] = $this->makeTeachingSetup();

        Student::factory()->count(2)->create(['class_id' => $class->id]);

        $session = AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => today()->toDateString(),
        ]);

        $assignment = Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'deadline' => now()->addDays(5),
            'title' => 'Tugas PKN Pramuka',
        ]);
        AssignmentSubmission::factory()->count(2)->create(['assignment_id' => $assignment->id]);

        $teacherUser = $teacher->user;

        $this->actingAs($teacherUser)
            ->get(route('dashboard.teacher'))
            ->assertOk()
            ->assertSee('Kelas yang Diampu')
            ->assertSee($class->name)
            ->assertSee($subject->name)
            ->assertSee('Sesi Absensi Hari Ini')
            ->assertSee('Berjalan')
            ->assertSee('Tugas Perlu Dinilai')
            ->assertSee('2 pengumpulan belum dinilai');
    }

    public function test_parent_dashboard_shows_selected_child_data_only(): void
    {
        [$year, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $parentUser = User::factory()->asParent()->create();
        $guardian = Guardian::factory()->create(['user_id' => $parentUser->id]);

        $childUser = User::factory()->asStudent()->create(['name' => 'Budi Santoso']);
        $child = Student::factory()->create(['user_id' => $childUser->id, 'class_id' => $class->id]);
        $guardian->students()->attach($child->id, ['relation' => 'Ayah', 'is_primary' => true]);

        $dates = [today()->subDays(1), today()->subDays(2), today()->subDays(3)];
        $first = AttendanceSession::factory()->create(['class_id' => $class->id, 'school_year_id' => $year->id, 'date' => $dates[0]->toDateString()]);
        AttendanceRecord::factory()->present()->create(['attendance_session_id' => $first->id, 'student_id' => $child->id]);
        $second = AttendanceSession::factory()->create(['class_id' => $class->id, 'school_year_id' => $year->id, 'date' => $dates[1]->toDateString()]);
        AttendanceRecord::factory()->late()->create(['attendance_session_id' => $second->id, 'student_id' => $child->id]);
        $third = AttendanceSession::factory()->create(['class_id' => $class->id, 'school_year_id' => $year->id, 'date' => $dates[2]->toDateString()]);
        AttendanceRecord::factory()->create([
            'attendance_session_id' => $third->id,
            'student_id' => $child->id,
            'status' => AttendanceStatus::Alfa,
        ]);

        $assignment = Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'deadline' => now()->addDays(2),
            'title' => 'Tugas Seni Menggambar',
        ]);
        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $child->id,
        ]);

        $this->actingAs($parentUser)
            ->get(route('dashboard.parent', ['student' => $child->id]))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Riwayat Absensi')
            ->assertSee('Tugas Anak')
            ->assertSee('Tugas Seni Menggambar')
            ->assertSee('Selesai')
            ->assertSee('Hadir')
            ->assertSee('Terlambat');
    }

    public function test_parent_cannot_select_a_student_that_is_not_linked_to_them(): void
    {
        [$year, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $otherClass = SchoolClass::factory()->create(['school_year_id' => $year->id]);
        $otherUser = User::factory()->asStudent()->create();
        $otherStudent = Student::factory()->create(['user_id' => $otherUser->id, 'class_id' => $otherClass->id]);
        Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $otherClass->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'deadline' => now()->addDays(2),
            'title' => 'Tugas Rahasia Siswa Lain',
        ]);

        $parentUser = User::factory()->asParent()->create();
        $guardian = Guardian::factory()->create(['user_id' => $parentUser->id]);
        $linkedUser = User::factory()->asStudent()->create(['name' => 'Anak Terhubung']);
        $linkedStudent = Student::factory()->create(['user_id' => $linkedUser->id, 'class_id' => $class->id]);
        $guardian->students()->attach($linkedStudent->id, ['relation' => 'Ibu', 'is_primary' => true]);

        $this->actingAs($parentUser)
            ->get(route('dashboard.parent', ['student' => $otherStudent->id]))
            ->assertOk()
            ->assertSee('Anak Terhubung')
            ->assertDontSee('Tugas Rahasia Siswa Lain');
    }

    public function test_admin_dashboard_shows_kpis_quick_actions_and_system_health(): void
    {
        Student::factory()->count(2)->create();
        Teacher::factory()->create();

        $adminUser = User::factory()->asSuperAdmin()->create();

        $this->actingAs($adminUser)
            ->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSee('Total Siswa')
            ->assertSee('Total Guru')
            ->assertSee('Kehadiran Hari Ini')
            ->assertSee('Notifikasi WA')
            ->assertSee('Tugas Aktif')
            ->assertSee('Aksi Cepat')
            ->assertSee('Tambah Siswa')
            ->assertSee('Tambah Guru')
            ->assertSee('Kesehatan Sistem')
            ->assertSee('Absensi Hari Ini')
            ->assertSee('Tugas Perlu Dinilai')
            ->assertSee('Notifikasi WhatsApp Terbaru')
            ->assertSee('Belum ada notifikasi')
            ->assertSee('Aktivitas Sistem Terbaru')
            ->assertSee('Belum ada aktivitas')
            ->assertSee('Buka')
            ->assertSee('Online')
            ->assertSee('Database')
            ->assertSee('Belum Digunakan');
    }

    public function test_admin_dashboard_shows_attendance_grading_queue_and_activity(): void
    {
        [$year, $teacher, $class, $subject] = $this->makeTeachingSetup();

        $studentUser = User::factory()->asStudent()->create();
        $student = Student::factory()->create(['user_id' => $studentUser->id, 'class_id' => $class->id]);

        $session = AttendanceSession::factory()->create([
            'class_id' => $class->id,
            'school_year_id' => $year->id,
            'date' => today()->toDateString(),
        ]);
        AttendanceRecord::factory()->present()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
        ]);

        $assignment = Assignment::factory()->published()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $year->id,
            'deadline' => now()->addDays(2),
            'title' => 'Tugas Sejarah Kerajaan',
        ]);
        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => SubmissionStatus::Submitted,
        ]);

        SystemLog::factory()->create([
            'module' => 'auth',
            'description' => 'Admin masuk ke sistem',
            'status' => SystemLogStatus::Success,
        ]);

        $adminUser = User::factory()->asSuperAdmin()->create();

        $this->actingAs($adminUser)
            ->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSee('Absensi Hari Ini')
            ->assertSee($class->name)
            ->assertSee('Berjalan')
            ->assertSee('Tugas Perlu Dinilai')
            ->assertSee('Tugas Sejarah Kerajaan')
            ->assertSee('1 pengumpulan menunggu penilaian')
            ->assertSee('Aktivitas Sistem Terbaru')
            ->assertSee('Admin masuk ke sistem');
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
