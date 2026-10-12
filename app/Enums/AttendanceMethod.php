<?php

namespace App\Enums;

enum AttendanceMethod: string
{
    case Button = 'button';
    case Qr = 'qr';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Button => 'Tombol Absen',
            self::Qr => 'Scan QR',
            self::Manual => 'Diubah Guru',
        };
    }
}
