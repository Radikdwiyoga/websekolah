<?php

namespace App\Enums;

enum AssignmentType: string
{
    case MultipleChoice = 'multiple_choice';
    case ShortAnswer = 'short_answer';
    case Essay = 'essay';
    case FileUpload = 'file_upload';

    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => 'Pilihan Ganda',
            self::ShortAnswer => 'Isian Singkat',
            self::Essay => 'Esai',
            self::FileUpload => 'Upload File',
        };
    }

    /**
     * Whether answers of this type are graded automatically.
     */
    public function isAutoGraded(): bool
    {
        return in_array($this, [self::MultipleChoice, self::ShortAnswer], true);
    }
}
