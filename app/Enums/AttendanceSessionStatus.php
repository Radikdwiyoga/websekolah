<?php

namespace App\Enums;

enum AttendanceSessionStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Berjalan',
            self::Closed => 'Ditutup',
        };
    }
}
