<?php

namespace App\Enums;

enum AssignmentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Dipublikasikan',
            self::Closed => 'Ditutup',
        };
    }

    /**
     * Badge tone used to render this status consistently across the UI.
     */
    public function variant(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Published => 'success',
            self::Closed => 'warning',
        };
    }
}
