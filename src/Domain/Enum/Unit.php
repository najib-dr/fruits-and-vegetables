<?php

namespace App\Domain\Enum;

enum Unit: string
{
    use EnumValuesTrait;

    case Gram = 'g';
    case Kilogram = 'kg';
}
