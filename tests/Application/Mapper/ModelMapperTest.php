<?php

namespace App\Tests\Application\Mapper;

use App\Application\Mapper\ModelMapper;
use App\Domain\Model\Fruit;
use App\Domain\Model\Vegetable;
use App\Tests\Builder\ItemDtoBuilder;
use PHPUnit\Framework\TestCase;

class ModelMapperTest extends TestCase
{
    private ModelMapper $unit;

    protected function setUp(): void
    {
        $this->unit = new ModelMapper();
    }

    public function testMapToFruit(): void
    {
        // Arrange
        $itemDto = ItemDtoBuilder::create()->build();

        // Act
        $fruit = $this->unit->mapToFruit($itemDto);

        // Assert
        $this->assertInstanceOf(Fruit::class, $fruit);
        $this->assertSame($itemDto->id, $fruit->getId());
        $this->assertSame($itemDto->name, $fruit->getName());
        $this->assertEquals($itemDto->quantity, $fruit->getQuantity());
        $this->assertSame($itemDto->unit, $fruit->getUnit()->value);
    }

    public function testMapToVegetable(): void
    {
        // Arrange
        $itemDto = ItemDtoBuilder::create()->build();

        // Act
        $vegetable = $this->unit->mapToVegetable($itemDto);

        // Assert
        $this->assertInstanceOf(Vegetable::class, $vegetable);
        $this->assertSame($itemDto->id, $vegetable->getId());
        $this->assertSame($itemDto->name, $vegetable->getName());
        $this->assertEquals($itemDto->quantity, $vegetable->getQuantity());
        $this->assertSame($itemDto->unit, $vegetable->getUnit()->value);
    }
}
