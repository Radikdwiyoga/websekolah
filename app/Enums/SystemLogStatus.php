<?php

namespace App\Enums;

enum SystemLogStatus: string
{
    case Success = 'success';
    case Failure = 'failure';

    public function label(): string
    {
        return match ($this) {
            self::Success => 'Berhasil',
            self::Failure => 'Gagal',
        };
    }

    public function variant(): string
    {
        return match ($this) {
            self::Success => 'success',
            self::Failure => 'danger',
        };
    }
}
