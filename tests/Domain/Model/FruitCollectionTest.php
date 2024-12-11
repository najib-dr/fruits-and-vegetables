<?php

namespace App\Tests\Domain\Model;

use App\Domain\Model\Fruit;
use App\Domain\Model\FruitCollection;
use PHPUnit\Framework\TestCase;

class FruitCollectionTest extends TestCase
{
    private FruitCollection $unit;

    protected function setUp(): void
    {
        $this->unit = new FruitCollection();
    }

    public function testAddFruit(): void
    {
        // Arrange
        $fruit = $this->createMock(Fruit::class);

        // Act
        $this->unit->add($fruit);

        // Assert
        $this->assertCount(1, $this->unit->list());
    }

    public function testRemoveFruit(): void
    {
        // Arrange
        $fruit = $this->createMock(Fruit::class);
        $fruit->method('getId')->willReturn(1);
        $this->unit->add($fruit);

        // Act
        $this->unit->remove(1);

        // Assert
        $this->assertCount(0, $this->unit->list());
    }

    public function testListFruits(): void
    {
        // Arrange
        $fruit1 = $this->createMock(Fruit::class);
        $fruit2 = $this->createMock(Fruit::class);
        $this->unit->add($fruit1);
        $this->unit->add($fruit2);

        // Act
        $fruits = $this->unit->list();

        // Assert
        $this->assertCount(2, $fruits);
    }

    public function testCountFruits(): void
    {
        // Arrange
        $fruit1 = $this->createMock(Fruit::class);
        $fruit2 = $this->createMock(Fruit::class);
        $this->unit->add($fruit1);
        $this->unit->add($fruit2);

        // Act
        $count = $this->unit->count();

        // Assert
        $this->assertEquals(2, $count);
    }
}
