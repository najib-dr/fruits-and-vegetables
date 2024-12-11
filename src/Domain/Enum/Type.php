<?php

namespace App\Domain\Enum;

enum Type: string
{
    use EnumValuesTrait;

    case Fruit = 'fruit';
    case Vegetable = 'vegetable';
}
