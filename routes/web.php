<?php

use App\Http\Controllers\AdminAnnouncementController;
use App\Http\Controllers\AdminAssignmentController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminClassController;
use App\Http\Controllers\AdminGuardianController;
use App\Http\Controllers\AdminMaintenanceController;
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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeAttendanceController;
use App\Http\Controllers\StudentAssignmentController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\StudentGradeController;
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

    Route::middleware('role:student')
        ->prefix('siswa/absensi')
        ->name('student.attendance.')
        ->group(function () {
            Route::get('/', [StudentAttendanceController::class, 'index'])->name('index');
            Route::post('{attendanceSession}/absen', [StudentAttendanceController::class, 'checkIn'])->name('check-in');
            Route::get('qr-kartu', [QrCodeAttendanceController::class, 'image'])->name('qr.image');
            Route::post('qr-check-in', [QrCodeAttendanceController::class, 'checkIn'])->name('qr.check-in');
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
            Route::get('guru', [AdminTeacherController::class, 'index'])->name('teachers.index');
            Route::get('orang-tua', [AdminGuardianController::class, 'index'])->name('guardians.index');
            Route::get('kelas', [AdminClassController::class, 'index'])->name('classes.index');
            Route::get('mata-pelajaran', [AdminSubjectController::class, 'index'])->name('subjects.index');
            Route::get('tahun-ajaran', [AdminSchoolYearController::class, 'index'])->name('school-years.index');
            Route::post('tahun-ajaran/{schoolYear}/aktifkan', [AdminSchoolYearController::class, 'activate'])->name('school-years.activate');

            Route::get('tugas', [AdminAssignmentController::class, 'index'])->name('assignments.index');

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
});

require __DIR__.'/auth.php';
