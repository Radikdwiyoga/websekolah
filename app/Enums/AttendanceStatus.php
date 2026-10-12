<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Hadir = 'hadir';
    case Terlambat = 'terlambat';
    case BelumAbsen = 'belum_absen';
    case Izin = 'izin';
    case Sakit = 'sakit';
    case Alfa = 'alfa';

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::Terlambat => 'Terlambat',
            self::BelumAbsen => 'Belum Absen',
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
            self::Alfa => 'Alfa',
        };
    }

    /**
     * Whether the student is considered physically present (on time or late).
     */
    public function isPresent(): bool
    {
        return in_array($this, [self::Hadir, self::Terlambat], true);
    }

    /**
     * Badge tone used to render the status in the UI.
     */
    public function variant(): string
    {
        return match ($this) {
            self::Hadir => 'success',
            self::Terlambat => 'warning',
            self::BelumAbsen => 'neutral',
            self::Izin, self::Sakit => 'info',
            self::Alfa => 'danger',
        };
    }
}
