<?php

namespace App\Domain\Converter;

use App\Domain\Enum\Unit;

class UnitConverter
{
    public static function toGrams(float $value, Unit $unit): int
    {
        return match ($unit) {
            Unit::Kilogram => (int) $value * 1000,
            Unit::Gram => $value,
        };
    }

    public static function gramToKilogram(float $value): float
    {
        return $value / 1000;
    }
}
