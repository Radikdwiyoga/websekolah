<?php

namespace App\Enums;

enum AnnouncementTarget: string
{
    case All = 'all';
    case Students = 'students';
    case Parents = 'parents';
    case ClassRoom = 'class';

    public function label(): string
    {
        return match ($this) {
            self::All => 'Semua Pengguna',
            self::Students => 'Siswa',
            self::Parents => 'Wali Murid',
            self::ClassRoom => 'Kelas Tertentu',
        };
    }

    /**
     * Badge tone used to render the target in the UI.
     */
    public function variant(): string
    {
        return match ($this) {
            self::All => 'primary',
            self::Students => 'info',
            self::Parents => 'info',
            self::ClassRoom => 'warning',
        };
    }
}
