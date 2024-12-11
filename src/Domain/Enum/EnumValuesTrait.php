<?php

namespace App\Domain\Enum;

trait EnumValuesTrait
{
    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
