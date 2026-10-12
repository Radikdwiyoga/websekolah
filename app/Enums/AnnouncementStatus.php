<?php

namespace App\Enums;

enum AnnouncementStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Scheduled => 'Dijadwalkan',
            self::Published => 'Dipublikasikan',
            self::Archived => 'Arsip',
        };
    }

    /**
     * Badge tone used to render the status in the UI.
     */
    public function variant(): string
    {
        return match ($this) {
            self::Published => 'success',
            self::Scheduled => 'warning',
            self::Draft => 'neutral',
            self::Archived => 'neutral',
        };
    }
}
