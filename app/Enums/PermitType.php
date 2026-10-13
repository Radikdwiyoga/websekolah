<?php

namespace App\Enums;

enum PermitType: string
{
    case Izin = 'izin';
    case Sakit = 'sakit';

    public function label(): string
    {
        return match ($this) {
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
        };
    }

    /**
     * The attendance status a student receives when this permit is approved.
     */
    public function attendanceStatus(): AttendanceStatus
    {
        return match ($this) {
            self::Izin => AttendanceStatus::Izin,
            self::Sakit => AttendanceStatus::Sakit,
        };
    }
}
