<?php

use App\Enums\UserRole;

/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
|
| Role-based sidebar menus (DESIGN.md §10, §38). Each item maps to a named
| route. Items whose route does not exist yet are rendered as disabled
| placeholders in the sidebar so the information architecture stays visible
| while features ship incrementally.
|
| 'active'  = route pattern(s) matched to highlight the current item.
| 'section' = optional sidebar group heading.
*/

$dashboard = static fn (string $route): array => [
    'label' => 'Dashboard',
    'icon' => 'dashboard',
    'route' => $route,
    'active' => ['dashboard.*'],
];

$shared = [
    'announcements' => [
        'label' => 'Pengumuman',
        'icon' => 'announcement',
        'route' => 'announcements.index',
        'active' => ['announcements.*'],
        'section' => 'Informasi',
    ],
    'profile' => [
        'label' => 'Profil Saya',
        'icon' => 'profile',
        'route' => 'profile.show',
        'active' => ['profile.*'],
        'section' => 'Akun',
    ],
];

return [
    UserRole::Admin->value => [
        [
            ...$dashboard('dashboard.admin'),
            'section' => 'Umum',
        ],

        ['label' => 'Data Siswa', 'icon' => 'students', 'route' => 'admin.students.index', 'active' => ['admin.students.*'], 'section' => 'Data Master'],
        ['label' => 'Data Guru', 'icon' => 'teacher', 'route' => 'admin.teachers.index', 'active' => ['admin.teachers.*'], 'section' => 'Data Master'],
        ['label' => 'Data Orang Tua', 'icon' => 'guardian', 'route' => 'admin.guardians.index', 'active' => ['admin.guardians.*'], 'section' => 'Data Master'],
        ['label' => 'Kelas', 'icon' => 'school', 'route' => 'admin.classes.index', 'active' => ['admin.classes.*'], 'section' => 'Data Master'],
        ['label' => 'Mata Pelajaran', 'icon' => 'book', 'route' => 'admin.subjects.index', 'active' => ['admin.subjects.*'], 'section' => 'Data Master'],
        ['label' => 'Tahun Ajaran', 'icon' => 'calendar', 'route' => 'admin.school-years.index', 'active' => ['admin.school-years.*'], 'section' => 'Data Master'],

        ['label' => 'Absensi', 'icon' => 'attendance', 'route' => 'admin.attendance.index', 'active' => ['admin.attendance.index'], 'section' => 'Akademik'],
        ['label' => 'Rekap Absensi', 'icon' => 'report', 'route' => 'admin.attendance.recap', 'active' => ['admin.attendance.recap']],
        ['label' => 'Tugas & Penilaian', 'icon' => 'task', 'route' => 'admin.assignments.index', 'active' => ['admin.assignments.*', 'admin.grades.*']],
        ['label' => 'Kelola Pengumuman', 'icon' => 'announcement', 'route' => 'admin.announcements.index', 'active' => ['admin.announcements.*'], 'section' => 'Informasi'],

        $shared['announcements'],

        ['label' => 'Pengaturan', 'icon' => 'settings', 'route' => 'admin.settings.index', 'active' => ['admin.settings.*'], 'section' => 'Administrasi'],
        ['label' => 'Maintenance', 'icon' => 'wrench', 'route' => 'admin.maintenance.index', 'active' => ['admin.maintenance.*']],
        ['label' => 'System Log', 'icon' => 'log', 'route' => 'admin.system-logs.index', 'active' => ['admin.system-logs.*']],

        $shared['profile'],
    ],

    UserRole::Teacher->value => [
        [
            ...$dashboard('dashboard.teacher'),
            'section' => 'Umum',
        ],

        ['label' => 'Kelas Saya', 'icon' => 'school', 'route' => 'teacher.classes.index', 'active' => ['teacher.classes.*'], 'section' => 'Akademik'],
        ['label' => 'Input Absensi', 'icon' => 'attendance', 'route' => 'teacher.attendance.index', 'active' => ['teacher.attendance.index', 'teacher.attendance.show', 'teacher.attendance.open', 'teacher.attendance.close', 'teacher.attendance.update']],
        ['label' => 'Rekap Absensi', 'icon' => 'report', 'route' => 'teacher.attendance.recap', 'active' => ['teacher.attendance.recap']],
        ['label' => 'Tugas', 'icon' => 'task', 'route' => 'teacher.assignments.index', 'active' => ['teacher.assignments.*']],
        ['label' => 'Penilaian', 'icon' => 'grade', 'route' => 'teacher.grades.index', 'active' => ['teacher.grades.*']],

        $shared['announcements'],
        $shared['profile'],
    ],

    UserRole::Student->value => [
        [
            ...$dashboard('dashboard.student'),
            'section' => 'Umum',
        ],

        ['label' => 'Kehadiran Saya', 'icon' => 'attendance', 'route' => 'student.attendance.index', 'active' => ['student.attendance.*'], 'section' => 'Akademik'],
        ['label' => 'Tugas', 'icon' => 'task', 'route' => 'student.assignments.index', 'active' => ['student.assignments.*']],
        ['label' => 'Nilai', 'icon' => 'grade', 'route' => 'student.grades.index', 'active' => ['student.grades.*']],

        $shared['announcements'],
        $shared['profile'],
    ],

    UserRole::Parent->value => [
        [
            ...$dashboard('dashboard.parent'),
            'section' => 'Umum',
        ],

        ['label' => 'Kehadiran Anak', 'icon' => 'attendance', 'route' => 'parent.attendance.index', 'active' => ['parent.attendance.*'], 'section' => 'Akademik'],
        ['label' => 'Tugas Anak', 'icon' => 'task', 'route' => 'parent.assignments.index', 'active' => ['parent.assignments.*']],
        ['label' => 'Nilai Anak', 'icon' => 'grade', 'route' => 'parent.grades.index', 'active' => ['parent.grades.*']],

        $shared['announcements'],
        $shared['profile'],
    ],
];
