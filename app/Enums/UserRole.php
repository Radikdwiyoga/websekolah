<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Teacher = 'teacher';
    case Parent = 'parent';
    case Admin = 'admin';

    /**
     * Human readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Student => 'Siswa',
            self::Teacher => 'Guru',
            self::Parent => 'Orang Tua',
            self::Admin => 'Admin Sekolah',
        };
    }

    /**
     * Named route of the role's dashboard.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Student => 'dashboard.student',
            self::Teacher => 'dashboard.teacher',
            self::Parent => 'dashboard.parent',
            self::Admin => 'dashboard.admin',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $role): string => $role->value, self::cases());
    }
}
