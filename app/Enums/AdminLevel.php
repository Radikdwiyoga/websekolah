<?php

namespace App\Enums;

enum AdminLevel: string
{
    case SuperAdmin = 'super_admin';
    case OperationalAdmin = 'operational_admin';

    /**
     * Human readable label for the admin level.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::OperationalAdmin => 'Admin Operasional',
        };
    }
}
