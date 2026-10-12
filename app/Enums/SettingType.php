<?php

namespace App\Enums;

enum SettingType: string
{
    case Text = 'text';
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Json = 'json';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Teks',
            self::Integer => 'Angka',
            self::Boolean => 'Boolean',
            self::Json => 'JSON',
        };
    }
}
