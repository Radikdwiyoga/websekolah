<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Graded = 'graded';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Dalam Pengerjaan',
            self::Submitted => 'Dikumpulkan',
            self::Graded => 'Dinilai',
        };
    }

    /**
     * Badge tone used to render this status consistently across the UI.
     */
    public function variant(): string
    {
        return match ($this) {
            self::Draft => 'info',
            self::Submitted => 'warning',
            self::Graded => 'success',
        };
    }
}
