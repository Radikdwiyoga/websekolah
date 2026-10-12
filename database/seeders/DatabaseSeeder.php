<?php

namespace Database\Seeders;

use App\Enums\AdminLevel;
use App\Enums\AnnouncementTarget as AnnouncementTargetEnum;
use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\AttendanceStatus;
use App\Enums\NotificationStatus;
use App\Enums\Semester;
use App\Enums\SettingType;
use App\Enums\SubmissionStatus;
use App\Enums\SystemLogStatus;
use App\Models\Announcement;
use App\Models\AnnouncementTarget;
use App\Models\ApplicationSetting;
use App\Models\Assignment;
use App\Models\AssignmentAnswer;
use App\Models\AssignmentQuestion;
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
use App\Models\WhatsAppNotificationLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedSettings();

        $schoolYear = $this->seedSchoolYear();
        $subjects = $this->seedSubjects();
        $teachers = $this->seedTeachers();
        $classes = $this->seedClasses($schoolYear, $teachers);
        $students = $this->seedStudents($classes);
        $this->seedGuardians($students);
        $this->seedTeachingAssignments($schoolYear, $teachers, $classes, $subjects);

        $this->seedAttendance($classes->first(), $schoolYear, $students->first());
        $this->seedAssignments($classes->first());

        $admin = $this->seedAdmins();
        $this->seedAnnouncements($admin, $classes->first());
        $this->seedAdminActivity($admin);
    }

    /**
     * Seed the default role-based demo accounts.
     *
     * @return Collection<int, Teacher>
     */
    private function seedAdmins(): User
    {
        User::factory()->asSuperAdmin()->create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@sekolah.test',
        ]);

        return User::factory()->asAdmin(AdminLevel::OperationalAdmin)->create([
            'name' => 'Admin Operasional',
            'username' => 'admin',
            'email' => 'admin@sekolah.test',
        ]);
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'school_name', 'value' => 'SMA Negeri 1 Nusantara', 'type' => SettingType::Text, 'group' => 'school'],
            ['key' => 'school_npsn', 'value' => '20123456', 'type' => SettingType::Text, 'group' => 'school'],
            ['key' => 'school_address', 'value' => 'Jl. Pendidikan No. 12, Nusantara', 'type' => SettingType::Text, 'group' => 'school'],
            ['key' => 'school_phone', 'value' => '021-1234567', 'type' => SettingType::Text, 'group' => 'school'],
            ['key' => 'school_email', 'value' => 'info@sman1nusantara.sch.id', 'type' => SettingType::Text, 'group' => 'school'],
            ['key' => 'headmaster_name', 'value' => 'Drs. Bambang Sutrisno, M.Pd.', 'type' => SettingType::Text, 'group' => 'school'],
            ['key' => 'attendance_open_time', 'value' => '07:00', 'type' => SettingType::Text, 'group' => 'attendance'],
            ['key' => 'attendance_close_time', 'value' => '09:00', 'type' => SettingType::Text, 'group' => 'attendance'],
            ['key' => 'attendance_late_after', 'value' => '07:15', 'type' => SettingType::Text, 'group' => 'attendance'],
            ['key' => 'default_grade_weight', 'value' => '100', 'type' => SettingType::Integer, 'group' => 'grading'],
            ['key' => 'whatsapp_message_template', 'value' => 'Assalamu\'alaikum, Bapak/Ibu {parent_name}. Ananda {student_name} dari kelas {class_name} tercatat {status} pada {date} pukul {time}. Terima kasih.', 'type' => SettingType::Text, 'group' => 'whatsapp'],
            ['key' => 'upload_max_size_kb', 'value' => '5120', 'type' => SettingType::Integer, 'group' => 'upload'],
            ['key' => 'upload_allowed_extensions', 'value' => '["pdf","jpg","jpeg","png","doc","docx"]', 'type' => SettingType::Json, 'group' => 'upload'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => SettingType::Boolean, 'group' => 'maintenance'],
            ['key' => 'maintenance_message', 'value' => 'Website sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.', 'type' => SettingType::Text, 'group' => 'maintenance'],
        ];

        foreach ($settings as $setting) {
            ApplicationSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }

    private function seedSchoolYear(): SchoolYear
    {
        return SchoolYear::factory()->active()->create([
            'name' => '2026/2027',
            'semester' => Semester::Ganjil,
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
        ]);
    }

    /**
     * @return Collection<int, Subject>
     */
    private function seedSubjects(): Collection
    {
        $subjects = [
            ['code' => 'MAT-101', 'name' => 'Matematika', 'description' => 'Aljabar, geometri, dan statistika dasar.'],
            ['code' => 'BIN-101', 'name' => 'Bahasa Indonesia', 'description' => 'Membaca, menulis, dan literasi.'],
            ['code' => 'BIG-101', 'name' => 'Bahasa Inggris', 'description' => 'Listening, speaking, reading, and writing.'],
            ['code' => 'IPA-101', 'name' => 'Ilmu Pengetahuan Alam', 'description' => 'Fisika, kimia, dan biologi dasar.'],
            ['code' => 'IPS-101', 'name' => 'Ilmu Pengetahuan Sosial', 'description' => 'Sejarah, geografi, dan ekonomi.'],
            ['code' => 'PKN-101', 'name' => 'Pendidikan Pancasila', 'description' => 'Pendidikan kewarganegaraan.'],
            ['code' => 'SEN-101', 'name' => 'Seni Budaya', 'description' => 'Seni rupa, musik, dan tari.'],
            ['code' => 'PJK-101', 'name' => 'Pendidikan Jasmani', 'description' => 'Olahraga dan kesehatan.'],
        ];

        return collect($subjects)->map(
            fn (array $subject): Subject => Subject::factory()->create($subject),
        );
    }

    /**
     * @return Collection<int, Teacher>
     */
    private function seedTeachers(): Collection
    {
        $teachers = collect([
            [
                'name' => 'Ibu Sari Dewi, S.Pd.',
                'username' => 'guru',
                'nip' => '198504122010012003',
                'gender' => 'P',
                'phone' => '081234567801',
            ],
            [
                'name' => 'Bapak Andi Pratama, S.Pd.',
                'username' => 'guru.andi',
                'nip' => '198709182011011005',
                'gender' => 'L',
                'phone' => '081234567802',
            ],
            [
                'name' => 'Ibu Rina Kartika, M.Pd.',
                'username' => 'guru.rina',
                'nip' => '199002052012012007',
                'gender' => 'P',
                'phone' => '081234567803',
            ],
            [
                'name' => 'Bapak Joko Susilo, S.Ag.',
                'username' => 'guru.joko',
                'nip' => '198811232013011009',
                'gender' => 'L',
                'phone' => '081234567804',
            ],
        ]);

        return $teachers->map(function (array $teacher): Teacher {
            $user = User::factory()->asTeacher()->create([
                'name' => $teacher['name'],
                'username' => $teacher['username'],
                'email' => $teacher['username'].'@sekolah.test',
            ]);

            return Teacher::factory()->create([
                'user_id' => $user->id,
                'nip' => $teacher['nip'],
                'gender' => $teacher['gender'],
                'phone' => $teacher['phone'],
                'is_active' => true,
            ]);
        });
    }

    /**
     * @param  Collection<int, Teacher>  $teachers
     * @return Collection<int, SchoolClass>
     */
    private function seedClasses(SchoolYear $schoolYear, Collection $teachers): Collection
    {
        $classes = [
            ['name' => 'X-1', 'level' => 'X', 'capacity' => 32],
            ['name' => 'X-2', 'level' => 'X', 'capacity' => 32],
            ['name' => 'XI-1', 'level' => 'XI', 'capacity' => 30],
            ['name' => 'XII-1', 'level' => 'XII', 'capacity' => 28],
        ];

        return collect($classes)
            ->values()
            ->map(fn (array $class, int $index): SchoolClass => SchoolClass::factory()->create([
                'school_year_id' => $schoolYear->id,
                'homeroom_teacher_id' => $teachers[$index % $teachers->count()]->id,
                'name' => $class['name'],
                'level' => $class['level'],
                'capacity' => $class['capacity'],
            ]));
    }

    /**
     * @param  Collection<int, SchoolClass>  $classes
     * @return Collection<int, Student>
     */
    private function seedStudents(Collection $classes): Collection
    {
        $firstClass = $classes->first();

        $user = User::factory()->asStudent()->create([
            'name' => 'Budi Santoso',
            'username' => 'siswa',
            'email' => 'siswa@sekolah.test',
        ]);

        $demoStudent = Student::factory()->create([
            'user_id' => $user->id,
            'class_id' => $firstClass->id,
            'nis' => '2026001',
            'nisn' => '0071234567',
            'gender' => 'L',
        ]);

        $students = collect([$demoStudent]);

        $classes->each(function (SchoolClass $class) use (&$students): void {
            $count = $class->level === 'X' ? 3 : 2;

            $students = $students->merge(
                Student::factory()->count($count)->create([
                    'class_id' => $class->id,
                ]),
            );
        });

        return $students;
    }

    /**
     * @param  Collection<int, Student>  $students
     */
    private function seedGuardians(Collection $students): void
    {
        $firstStudent = $students->first();

        $user = User::factory()->asParent()->create([
            'name' => 'Ibu Melati',
            'username' => 'ortu',
            'email' => 'ortu@sekolah.test',
        ]);

        $guardian = Guardian::factory()->create([
            'user_id' => $user->id,
            'phone' => '081298765432',
            'occupation' => 'Ibu Rumah Tangga',
        ]);

        $guardian->students()->attach($firstStudent->id, [
            'relation' => 'Ibu',
            'is_primary' => true,
        ]);

        Guardian::factory()->count(6)->create()->each(function (Guardian $extra): void {
            $student = Student::query()->inRandomOrder()->first();

            if (! $student) {
                return;
            }

            $hasPrimary = $student->guardians()->wherePivot('is_primary', true)->exists();

            $extra->students()->attach($student->id, [
                'relation' => 'Wali',
                'is_primary' => ! $hasPrimary,
            ]);
        });
    }

    /**
     * @param  Collection<int, Teacher>  $teachers
     * @param  Collection<int, SchoolClass>  $classes
     * @param  Collection<int, Subject>  $subjects
     */
    private function seedTeachingAssignments(
        SchoolYear $schoolYear,
        Collection $teachers,
        Collection $classes,
        Collection $subjects,
    ): void {
        $classes->values()->each(function (SchoolClass $class, int $classIndex) use ($schoolYear, $teachers, $subjects): void {
            $subjects->values()->each(function (Subject $subject, int $subjectIndex) use ($schoolYear, $teachers, $class, $classIndex): void {
                TeachingAssignment::factory()->create([
                    'teacher_id' => $teachers[($classIndex + $subjectIndex) % $teachers->count()]->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'school_year_id' => $schoolYear->id,
                ]);
            });
        });
    }

    private function seedAnnouncements(User $admin, ?SchoolClass $firstClass): void
    {
        Announcement::factory()->published()->important()->create([
            'title' => 'Selamat Datang di Tahun Ajaran 2026/2027',
            'body' => 'Selamat datang kembali, siswa dan wali murid. Semoga tahun ajaran baru ini membawa semangat belajar yang baru. Kegiatan belajar dimulai pukul 07.00 WIB.',
            'target_type' => AnnouncementTargetEnum::All,
            'created_by' => $admin->id,
        ]);

        Announcement::factory()->published()->create([
            'title' => 'Jadwal Ujian Tengah Semester',
            'body' => 'Ujian Tengah Semester akan dilaksanakan pada dua minggu pertama bulan Oktober. Jadwal lengkap dapat dilihat pada papan pengumuman kelas.',
            'target_type' => AnnouncementTargetEnum::Students,
            'created_by' => $admin->id,
        ]);

        Announcement::factory()->create([
            'title' => 'Persiapan Akreditasi Sekolah',
            'body' => 'Dalam rangka persiapan akreditasi, seluruh warga sekolah diharapkan menjaga kebersihan dan ketertiban lingkungan sekolah. Detail kegiatan menyusul.',
            'target_type' => AnnouncementTargetEnum::All,
            'is_important' => true,
            'created_by' => $admin->id,
        ]);

        if (! $firstClass) {
            return;
        }

        $classAnnouncement = Announcement::factory()->published()->create([
            'title' => 'Rapat Wali Murid Kelas '.$firstClass->name,
            'body' => 'Diberitahukan kepada wali murid kelas '.$firstClass->name.' bahwa akan diadakan rapat pada hari Sabtu pukul 09.00 WIB di aula sekolah.',
            'target_type' => AnnouncementTargetEnum::ClassRoom,
            'created_by' => $admin->id,
        ]);

        AnnouncementTarget::factory()->forClass($firstClass)->create([
            'announcement_id' => $classAnnouncement->id,
        ]);
    }

    /**
     * Seed recent system activity and WhatsApp notification logs so the admin
     * dashboard has operational data out of the box.
     */
    private function seedAdminActivity(User $admin): void
    {
        $logs = [
            ['module' => 'auth', 'action' => 'login', 'description' => 'Super Admin masuk ke sistem', 'status' => SystemLogStatus::Success],
            ['module' => 'attendance', 'action' => 'create', 'description' => 'Sesi absensi kelas X-1 dibuka', 'status' => SystemLogStatus::Success],
            ['module' => 'assignment', 'action' => 'create', 'description' => 'Tugas Matematika dipublikasikan', 'status' => SystemLogStatus::Success],
            ['module' => 'whatsapp', 'action' => 'send', 'description' => 'Notifikasi keterlambatan gagal terkirim', 'status' => SystemLogStatus::Failure],
        ];

        foreach ($logs as $log) {
            SystemLog::factory()->create($log + ['user_id' => $admin->id]);
        }

        WhatsAppNotificationLog::factory()->sent()->create([
            'recipient_phone' => '081298765432',
            'message' => 'Notifikasi kehadiran Budi Santoso telah dikirim ke wali murid.',
        ]);

        WhatsAppNotificationLog::factory()->sent()->create([
            'recipient_phone' => '081234567801',
            'message' => 'Pengumuman tahun ajaran baru telah dikirim ke wali murid.',
        ]);

        WhatsAppNotificationLog::factory()->create([
            'recipient_phone' => '081298765432',
            'message' => 'Notifikasi tugas Matematika menunggu dikirim.',
            'status' => NotificationStatus::Pending,
        ]);
    }

    /**
     * Seed a week of past attendance sessions for the first class so the
     * teacher and parent attendance views have history to show.
     */
    private function seedAttendance(?SchoolClass $class, SchoolYear $schoolYear, ?Student $demoStudent): void
    {
        if (! $class || ! $demoStudent) {
            return;
        }

        $students = Student::query()->where('class_id', $class->id)->get();

        $demoStatuses = [
            AttendanceStatus::Hadir,
            AttendanceStatus::Hadir,
            AttendanceStatus::Terlambat,
            AttendanceStatus::Hadir,
            AttendanceStatus::Izin,
        ];

        $day = now()->subDay();
        $index = 0;

        while ($index < count($demoStatuses)) {
            if ($day->isWeekend()) {
                $day = $day->subDay();

                continue;
            }

            $session = AttendanceSession::factory()->closed()->create([
                'class_id' => $class->id,
                'school_year_id' => $schoolYear->id,
                'date' => $day->toDateString(),
            ]);

            foreach ($students as $student) {
                $status = $student->is($demoStudent) ? $demoStatuses[$index] : AttendanceStatus::Hadir;

                AttendanceRecord::factory()->create([
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                    'status' => $status,
                    'checked_in_at' => $status->isPresent() ? $day->copy()->setTime(7, 0) : null,
                ]);
            }

            $index++;
            $day = $day->subDay();
        }
    }

    /**
     * Seed demo assignments (one per supported type) for the first class so the
     * teacher and student dashboards have meaningful data out of the box.
     */
    private function seedAssignments(?SchoolClass $class): void
    {
        if (! $class) {
            return;
        }

        $teachings = TeachingAssignment::query()
            ->where('class_id', $class->id)
            ->with(['teacher', 'subject'])
            ->get();

        if ($teachings->isEmpty()) {
            return;
        }

        $primary = $teachings->first();

        $multipleChoice = Assignment::factory()->published()->create([
            'teacher_id' => $primary->teacher_id,
            'class_id' => $class->id,
            'subject_id' => $primary->subject_id,
            'school_year_id' => $class->school_year_id,
            'title' => 'Latihan Pilihan Ganda '.$primary->subject->name,
            'description' => 'Kerjakan soal pilihan ganda berikut dengan teliti.',
            'type' => AssignmentType::MultipleChoice,
            'deadline' => now()->addDays(5),
            'late_deduction_percent' => 10,
        ]);

        $multipleChoiceQuestions = [
            [
                'question_text' => 'Hasil dari 2 + 3 x 4 adalah ...',
                'options' => ['A' => '14', 'B' => '20', 'C' => '24', 'D' => '11'],
                'correct_answers' => ['A'],
                'points' => 50,
            ],
            [
                'question_text' => 'Bilangan prima terkecil adalah ...',
                'options' => ['A' => '0', 'B' => '1', 'C' => '2', 'D' => '3'],
                'correct_answers' => ['C'],
                'points' => 50,
            ],
        ];

        foreach ($multipleChoiceQuestions as $index => $question) {
            AssignmentQuestion::query()->create([
                'assignment_id' => $multipleChoice->id,
                'order' => $index + 1,
                'type' => AssignmentType::MultipleChoice,
                ...$question,
            ]);
        }

        $essay = Assignment::factory()->published()->create([
            'teacher_id' => $primary->teacher_id,
            'class_id' => $class->id,
            'subject_id' => $primary->subject_id,
            'school_year_id' => $class->school_year_id,
            'title' => 'Esai Singkat '.$primary->subject->name,
            'description' => 'Tuliskan jawaban Anda secara ringkas dan jelas.',
            'type' => AssignmentType::Essay,
            'deadline' => now()->addDays(3),
            'late_deduction_percent' => 15,
        ]);

        $essayQuestions = [
            'Jelaskan pengertian persamaan linear satu variabel beserta contohnya.',
            'Sebutkan langkah-langkah menyelesaikan sistem persamaan linear dua variabel.',
        ];

        foreach ($essayQuestions as $index => $questionText) {
            AssignmentQuestion::query()->create([
                'assignment_id' => $essay->id,
                'order' => $index + 1,
                'type' => AssignmentType::Essay,
                'question_text' => $questionText,
                'options' => null,
                'correct_answers' => null,
                'points' => 50,
            ]);
        }

        $student = Student::query()->where('class_id', $class->id)->first();

        if ($student) {
            $submission = AssignmentSubmission::query()->create([
                'assignment_id' => $essay->id,
                'student_id' => $student->id,
                'submitted_at' => now()->subHours(2),
                'is_late' => false,
                'status' => SubmissionStatus::Submitted,
            ]);

            $firstEssayQuestion = $essay->questions()->first();

            if ($firstEssayQuestion) {
                AssignmentAnswer::query()->create([
                    'assignment_submission_id' => $submission->id,
                    'assignment_question_id' => $firstEssayQuestion->id,
                    'answer_text' => 'Persamaan linear satu variabel adalah persamaan yang memuat satu variabel berpangkat satu, misalnya 2x + 3 = 7.',
                ]);
            }
        }

        if ($student) {
            $teachings->take(3)->each(function (TeachingAssignment $teaching) use ($student, $class): void {
                Grade::factory()->create([
                    'student_id' => $student->id,
                    'subject_id' => $teaching->subject_id,
                    'class_id' => $class->id,
                    'school_year_id' => $class->school_year_id,
                    'score' => fake()->numberBetween(75, 95),
                ]);
            });
        }

        Assignment::factory()->published()->create([
            'teacher_id' => $primary->teacher_id,
            'class_id' => $class->id,
            'subject_id' => $primary->subject_id,
            'school_year_id' => $class->school_year_id,
            'title' => 'Tugas Unggah Berkas '.$primary->subject->name,
            'description' => 'Unggah berkas hasil pekerjaan Anda dalam format PDF atau gambar.',
            'type' => AssignmentType::FileUpload,
            'deadline' => now()->addWeek(),
            'late_deduction_percent' => 20,
        ]);

        Assignment::factory()->create([
            'teacher_id' => $primary->teacher_id,
            'class_id' => $class->id,
            'subject_id' => $primary->subject_id,
            'school_year_id' => $class->school_year_id,
            'title' => 'Draft Tugas Mendatang',
            'description' => 'Tugas ini masih berupa draft dan belum dipublikasikan.',
            'type' => AssignmentType::ShortAnswer,
            'status' => AssignmentStatus::Draft,
            'deadline' => now()->addWeeks(2),
        ]);
    }
}
