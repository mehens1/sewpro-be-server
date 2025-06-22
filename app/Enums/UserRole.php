<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
