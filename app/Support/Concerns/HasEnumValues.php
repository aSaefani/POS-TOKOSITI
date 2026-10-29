<?php

namespace App\Support\Concerns;

trait HasEnumValues
{
    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
