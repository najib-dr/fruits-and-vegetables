<?php

namespace App\Tests\Domain\Converter;

use App\Domain\Converter\UnitConverter;
use App\Domain\Enum\Unit;
use PHPUnit\Framework\TestCase;

class UnitConverterTest extends TestCase
{
    public function testToGramsWithKilogram(): void
    {
        // Arrange
        $valueInKilograms = 2;
        $expectedValueInGrams = 2000;

        // Act
        $result = UnitConverter::toGrams($valueInKilograms, Unit::Kilogram);

        // Assert
        $this->assertEquals($expectedValueInGrams, $result);
    }

    public function testToGramsWithGram(): void
    {
        // Arrange
        $valueInGrams = 500;
        $expectedValueInGrams = 500;

        // Act
        $result = UnitConverter::toGrams($valueInGrams, Unit::Gram);

        // Assert
        $this->assertEquals($expectedValueInGrams, $result);
    }
}
