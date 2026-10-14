<?php

use App\Http\Controllers\AdminAgendaController;
use App\Http\Controllers\AdminAnnouncementController;
use App\Http\Controllers\AdminAssignmentController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminBackupController;
use App\Http\Controllers\AdminClassController;
use App\Http\Controllers\AdminGuardianController;
use App\Http\Controllers\AdminHomepageController;
use App\Http\Controllers\AdminMaintenanceController;
use App\Http\Controllers\AdminSchoolProfileController;
use App\Http\Controllers\AdminSchoolYearController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\AdminSubjectController;
use App\Http\Controllers\AdminSystemLogController;
use App\Http\Controllers\AdminTeacherController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParentAssignmentController;
use App\Http\Controllers\ParentAttendanceController;
use App\Http\Controllers\ParentGradeController;
use App\Http\Controllers\ParentPermitController;
use App\Http\Controllers\PermitAttachmentController;
use App\Http\Controllers\PermitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeAttendanceController;
use App\Http\Controllers\StudentAssignmentController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\StudentGradeController;
use App\Http\Controllers\StudentPermitController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\TeacherClassController;
use App\Http\Controllers\TeacherGradeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Publicly accessible announcement preview and details (controller checks access if not public)
Route::prefix('pengumuman')->name('announcements.')->group(function () {
    Route::get('{announcement}/file', [AnnouncementController::class, 'file'])->name('file');
    Route::get('{announcement}/berkas', [AnnouncementController::class, 'attachment'])->name('attachment');
    Route::get('{announcement}', [AnnouncementController::class, 'show'])->name('show');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

    Route::get('/dashboard/student', [DashboardController::class, 'student'])
        ->middleware('role:student')
        ->name('dashboard.student');

    Route::get('/dashboard/teacher', [DashboardController::class, 'teacher'])
        ->middleware('role:teacher')
        ->name('dashboard.teacher');

    Route::get('/dashboard/parent', [DashboardController::class, 'parent'])
        ->middleware('role:parent')
        ->name('dashboard.parent');

    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('dashboard.admin');

    // Profil Pengguna (Semua Peran)
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Guru & Admin - Kelas Saya
    Route::middleware('role:teacher,admin')
        ->prefix('guru/kelas')
        ->name('teacher.classes.')
        ->group(function () {
            Route::get('/', [TeacherClassController::class, 'index'])->name('index');
        });

    Route::middleware('role:teacher,admin')
        ->prefix('guru/absensi')
        ->name('teacher.attendance.')
        ->group(function () {
            Route::get('/', [TeacherAttendanceController::class, 'index'])->name('index');
            Route::get('rekap', [TeacherAttendanceController::class, 'recap'])->name('recap');
            Route::get('rekap/ekspor', [TeacherAttendanceController::class, 'recapExport'])->name('recap.export');
            Route::get('{schoolClass}', [TeacherAttendanceController::class, 'show'])->name('show');
            Route::post('{schoolClass}/buka', [TeacherAttendanceController::class, 'open'])->name('open');
            Route::post('sesi/{attendanceSession}/tutup', [TeacherAttendanceController::class, 'close'])->name('close');
            Route::post('sesi/{attendanceSession}/perbarui', [TeacherAttendanceController::class, 'update'])->name('update');
            Route::post('sesi/{attendanceSession}/scan-qr', [QrCodeAttendanceController::class, 'scan'])->name('scan-qr');
        });

    // Guru & Admin - Izin & Sakit (tinjau pengajuan)
    Route::middleware('role:teacher,admin')
        ->prefix('guru/izin')
        ->name('teacher.permits.')
        ->group(function () {
            Route::get('/', [PermitController::class, 'index'])->name('index');
            Route::post('{permit}/tinjau', [PermitController::class, 'review'])->name('review');
        });

    // Izin & Sakit - unduh lampiran (semua peran yang berhak)
    Route::get('izin/{permit}/berkas', PermitAttachmentController::class)->name('permits.attachment');

    Route::middleware('role:student')
        ->prefix('siswa/absensi')
        ->name('student.attendance.')
        ->group(function () {
            Route::get('/', [StudentAttendanceController::class, 'index'])->name('index');
            Route::post('{attendanceSession}/absen', [StudentAttendanceController::class, 'checkIn'])->name('check-in');
            Route::get('qr-kartu', [QrCodeAttendanceController::class, 'image'])->name('qr.image');
            Route::post('qr-check-in', [QrCodeAttendanceController::class, 'checkIn'])->name('qr.check-in');
        });

    // Siswa - Izin & Sakit (pengajuan pribadi)
    Route::middleware('role:student')
        ->prefix('siswa/izin')
        ->name('student.permits.')
        ->group(function () {
            Route::get('/', [StudentPermitController::class, 'index'])->name('index');
            Route::post('/', [StudentPermitController::class, 'store'])->name('store');
            Route::delete('{permit}', [StudentPermitController::class, 'destroy'])->name('destroy');
        });

    Route::middleware('role:teacher,admin')
        ->prefix('guru/tugas')
        ->name('teacher.assignments.')
        ->group(function () {
            Route::get('/', [TeacherAssignmentController::class, 'index'])->name('index');
            Route::get('buat', [TeacherAssignmentController::class, 'create'])->name('create');
            Route::post('/', [TeacherAssignmentController::class, 'store'])->name('store');
            Route::get('{assignment}', [TeacherAssignmentController::class, 'show'])->name('show');
            Route::get('{assignment}/ubah', [TeacherAssignmentController::class, 'edit'])->name('edit');
            Route::put('{assignment}', [TeacherAssignmentController::class, 'update'])->name('update');
            Route::delete('{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('destroy');
            Route::post('{assignment}/terbitkan', [TeacherAssignmentController::class, 'publish'])->name('publish');
            Route::post('{assignment}/tutup', [TeacherAssignmentController::class, 'close'])->name('close');
            Route::post('{assignment}/soal', [TeacherAssignmentController::class, 'storeQuestions'])->name('questions.store');
            Route::delete('{assignment}/soal/{question}', [TeacherAssignmentController::class, 'destroyQuestion'])->name('questions.destroy');
            Route::get('{assignment}/pengumpulan/{submission}/berkas', [TeacherAssignmentController::class, 'submissionFile'])->name('submissions.file');
        });

    Route::middleware('role:teacher,admin')
        ->prefix('guru/penilaian')
        ->name('teacher.grades.')
        ->group(function () {
            Route::get('/', [TeacherGradeController::class, 'index'])->name('index');
            Route::get('{submission}/nilai', [TeacherGradeController::class, 'edit'])->name('edit');
            Route::put('{submission}', [TeacherGradeController::class, 'update'])->name('update');
        });

    Route::middleware('role:student')
        ->prefix('siswa/tugas')
        ->name('student.assignments.')
        ->group(function () {
            Route::get('/', [StudentAssignmentController::class, 'index'])->name('index');
            Route::get('{assignment}', [StudentAssignmentController::class, 'show'])->name('show');
            Route::post('{assignment}/kumpulkan', [StudentAssignmentController::class, 'save'])->name('save');
            Route::get('{assignment}/berkas', [StudentAssignmentController::class, 'file'])->name('file');
        });

    // Admin - Data Master
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('siswa', [AdminStudentController::class, 'index'])->name('students.index');
            Route::get('siswa/tambah', [AdminStudentController::class, 'create'])->name('students.create');
            Route::post('siswa', [AdminStudentController::class, 'store'])->name('students.store');
            Route::get('siswa/{student}/ubah', [AdminStudentController::class, 'edit'])->name('students.edit');
            Route::put('siswa/{student}', [AdminStudentController::class, 'update'])->name('students.update');
            Route::patch('siswa/{student}/status', [AdminStudentController::class, 'toggleStatus'])->name('students.toggle-status');

            Route::get('guru', [AdminTeacherController::class, 'index'])->name('teachers.index');
            Route::get('guru/tambah', [AdminTeacherController::class, 'create'])->name('teachers.create');
            Route::post('guru', [AdminTeacherController::class, 'store'])->name('teachers.store');
            Route::get('guru/{teacher}/ubah', [AdminTeacherController::class, 'edit'])->name('teachers.edit');
            Route::put('guru/{teacher}', [AdminTeacherController::class, 'update'])->name('teachers.update');
            Route::patch('guru/{teacher}/status', [AdminTeacherController::class, 'toggleStatus'])->name('teachers.toggle-status');
            Route::post('guru/{teacher}/penugasan', [AdminTeacherController::class, 'assignTeaching'])->name('teachers.assign');
            Route::delete('guru/{teacher}/penugasan/{assignment}', [AdminTeacherController::class, 'removeTeaching'])->name('teachers.unassign');

            Route::get('orang-tua', [AdminGuardianController::class, 'index'])->name('guardians.index');
            Route::get('orang-tua/tambah', [AdminGuardianController::class, 'create'])->name('guardians.create');
            Route::post('orang-tua', [AdminGuardianController::class, 'store'])->name('guardians.store');
            Route::get('orang-tua/{guardian}/ubah', [AdminGuardianController::class, 'edit'])->name('guardians.edit');
            Route::put('orang-tua/{guardian}', [AdminGuardianController::class, 'update'])->name('guardians.update');
            Route::patch('orang-tua/{guardian}/status', [AdminGuardianController::class, 'toggleStatus'])->name('guardians.toggle-status');
            Route::post('orang-tua/{guardian}/anak', [AdminGuardianController::class, 'attachStudent'])->name('guardians.attach-student');
            Route::delete('orang-tua/{guardian}/anak/{student}', [AdminGuardianController::class, 'detachStudent'])->name('guardians.detach-student');

            Route::get('kelas', [AdminClassController::class, 'index'])->name('classes.index');
            Route::get('kelas/tambah', [AdminClassController::class, 'create'])->name('classes.create');
            Route::post('kelas', [AdminClassController::class, 'store'])->name('classes.store');
            Route::get('kelas/{schoolClass}', [AdminClassController::class, 'show'])->name('classes.show');
            Route::get('kelas/{schoolClass}/ubah', [AdminClassController::class, 'edit'])->name('classes.edit');
            Route::put('kelas/{schoolClass}', [AdminClassController::class, 'update'])->name('classes.update');

            Route::get('mata-pelajaran', [AdminSubjectController::class, 'index'])->name('subjects.index');
            Route::get('mata-pelajaran/tambah', [AdminSubjectController::class, 'create'])->name('subjects.create');
            Route::post('mata-pelajaran', [AdminSubjectController::class, 'store'])->name('subjects.store');
            Route::get('mata-pelajaran/{subject}/ubah', [AdminSubjectController::class, 'edit'])->name('subjects.edit');
            Route::put('mata-pelajaran/{subject}', [AdminSubjectController::class, 'update'])->name('subjects.update');

            Route::get('tahun-ajaran', [AdminSchoolYearController::class, 'index'])->name('school-years.index');
            Route::get('tahun-ajaran/tambah', [AdminSchoolYearController::class, 'create'])->name('school-years.create');
            Route::post('tahun-ajaran', [AdminSchoolYearController::class, 'store'])->name('school-years.store');
            Route::post('tahun-ajaran/{schoolYear}/aktifkan', [AdminSchoolYearController::class, 'activate'])->name('school-years.activate');
            Route::get('tahun-ajaran/{schoolYear}/ubah', [AdminSchoolYearController::class, 'edit'])->name('school-years.edit');
            Route::put('tahun-ajaran/{schoolYear}', [AdminSchoolYearController::class, 'update'])->name('school-years.update');

            Route::get('tugas', [AdminAssignmentController::class, 'index'])->name('assignments.index');

            Route::get('agenda', [AdminAgendaController::class, 'index'])->name('agendas.index');
            Route::get('agenda/tambah', [AdminAgendaController::class, 'create'])->name('agendas.create');
            Route::post('agenda', [AdminAgendaController::class, 'store'])->name('agendas.store');
            Route::get('agenda/{agenda}/ubah', [AdminAgendaController::class, 'edit'])->name('agendas.edit');
            Route::put('agenda/{agenda}', [AdminAgendaController::class, 'update'])->name('agendas.update');
            Route::delete('agenda/{agenda}', [AdminAgendaController::class, 'destroy'])->name('agendas.destroy');
            Route::patch('agenda/{agenda}/status', [AdminAgendaController::class, 'togglePublished'])->name('agendas.toggle-status');

            Route::get('maintenance', [AdminMaintenanceController::class, 'index'])->name('maintenance.index');
            Route::post('maintenance/toggle', [AdminMaintenanceController::class, 'toggle'])->name('maintenance.toggle');
            Route::post('maintenance/clear-cache', [AdminMaintenanceController::class, 'clearCache'])->name('maintenance.clear-cache');

            Route::get('system-logs', [AdminSystemLogController::class, 'index'])->name('system-logs.index');
        });

    Route::middleware('role:admin')
        ->prefix('admin/absensi')
        ->name('admin.attendance.')
        ->group(function () {
            Route::get('/', [AdminAttendanceController::class, 'index'])->name('index');
            Route::get('rekap', [AdminAttendanceController::class, 'recap'])->name('recap');
            Route::get('rekap/ekspor', [AdminAttendanceController::class, 'recapExport'])->name('recap.export');
        });

    Route::middleware('role:admin')
        ->prefix('admin/backup')
        ->name('admin.backups.')
        ->group(function () {
            Route::get('/', [AdminBackupController::class, 'index'])->name('index');
            Route::get('absensi', [AdminBackupController::class, 'attendance'])->name('attendance');
            Route::get('tugas-nilai', [AdminBackupController::class, 'assignments'])->name('assignments');
        });

    Route::middleware('role:student')
        ->prefix('siswa')
        ->name('student.')
        ->group(function () {
            Route::get('nilai', [StudentGradeController::class, 'index'])->name('grades.index');
        });

    Route::middleware('auth')
        ->prefix('pengumuman')
        ->name('announcements.')
        ->group(function () {
            Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        });

    Route::middleware('role:admin')
        ->prefix('admin/pengumuman')
        ->name('admin.announcements.')
        ->group(function () {
            Route::get('/', [AdminAnnouncementController::class, 'index'])->name('index');
            Route::get('buat', [AdminAnnouncementController::class, 'create'])->name('create');
            Route::post('/', [AdminAnnouncementController::class, 'store'])->name('store');
            Route::get('{announcement}', [AdminAnnouncementController::class, 'show'])->name('show');
            Route::get('{announcement}/ubah', [AdminAnnouncementController::class, 'edit'])->name('edit');
            Route::put('{announcement}', [AdminAnnouncementController::class, 'update'])->name('update');
            Route::delete('{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('destroy');
            Route::post('{announcement}/terbitkan', [AdminAnnouncementController::class, 'publish'])->name('publish');
            Route::post('{announcement}/arsipkan', [AdminAnnouncementController::class, 'archive'])->name('archive');
        });

    Route::middleware('role:admin')
        ->prefix('settings')
        ->name('admin.settings.')
        ->group(function () {
            Route::get('sekolah', [AdminSchoolProfileController::class, 'edit'])->name('school.edit');
            Route::put('sekolah', [AdminSchoolProfileController::class, 'update'])->name('school.update');
            Route::get('halaman-utama', [AdminHomepageController::class, 'edit'])->name('homepage.edit');
            Route::put('halaman-utama', [AdminHomepageController::class, 'update'])->name('homepage.update');
            Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
            Route::get('{key}/edit', [AdminSettingsController::class, 'edit'])->name('edit');
            Route::post('{key}/update', [AdminSettingsController::class, 'update'])->name('update');
        });

    Route::middleware('role:parent')
        ->prefix('orangtua')
        ->name('parent.')
        ->group(function () {
            Route::get('kehadiran', [ParentAttendanceController::class, 'index'])->name('attendance.index');
            Route::get('tugas', [ParentAssignmentController::class, 'index'])->name('assignments.index');
            Route::get('nilai', [ParentGradeController::class, 'index'])->name('grades.index');
        });

    // Orang Tua - Izin & Sakit (pengajuan untuk anak)
    Route::middleware('role:parent')
        ->prefix('orangtua/izin')
        ->name('parent.permits.')
        ->group(function () {
            Route::get('/', [ParentPermitController::class, 'index'])->name('index');
            Route::post('/', [ParentPermitController::class, 'store'])->name('store');
            Route::delete('{permit}', [ParentPermitController::class, 'destroy'])->name('destroy');
        });
});

require __DIR__.'/auth.php';
