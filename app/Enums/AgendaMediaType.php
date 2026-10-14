<?php

namespace App\Enums;

enum AgendaMediaType: string
{
    case Photo = 'photo';
    case Video = 'video';

    /**
     * Human readable label for the media type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Photo => 'Foto',
            self::Video => 'Video',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }
}
