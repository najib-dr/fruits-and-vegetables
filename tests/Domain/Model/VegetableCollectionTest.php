<?php

namespace App\Tests\Domain\Model;

use App\Domain\Model\Vegetable;
use App\Domain\Model\VegetableCollection;
use PHPUnit\Framework\TestCase;

class VegetableCollectionTest extends TestCase
{
    private VegetableCollection $unit;

    protected function setUp(): void
    {
        $this->unit = new VegetableCollection();
    }

    public function testAddVegetable(): void
    {
        // Arrange
        $vegetable = $this->createMock(Vegetable::class);

        // Act
        $this->unit->add($vegetable);

        // Assert
        $this->assertCount(1, $this->unit->list());
    }

    public function testRemoveVegetable(): void
    {
        // Arrange
        $vegetable = $this->createMock(Vegetable::class);
        $vegetable->method('getId')->willReturn(1);
        $this->unit->add($vegetable);

        // Act
        $this->unit->remove(1);

        // Assert
        $this->assertCount(0, $this->unit->list());
    }

    public function testListVegetables(): void
    {
        // Arrange
        $vegetable1 = $this->createMock(Vegetable::class);
        $vegetable2 = $this->createMock(Vegetable::class);
        $this->unit->add($vegetable1);
        $this->unit->add($vegetable2);

        // Act
        $vegetables = $this->unit->list();

        // Assert
        $this->assertCount(2, $vegetables);
    }

    public function testCountVegetables(): void
    {
        // Arrange
        $vegetable1 = $this->createMock(Vegetable::class);
        $vegetable2 = $this->createMock(Vegetable::class);
        $this->unit->add($vegetable1);
        $this->unit->add($vegetable2);

        // Act
        $count = $this->unit->count();

        // Assert
        $this->assertSame(2, $count);
    }
}
