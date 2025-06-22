<?php

namespace App\Enums;

enum AccountType: string
{
    case TAILOR = 'tailor';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
