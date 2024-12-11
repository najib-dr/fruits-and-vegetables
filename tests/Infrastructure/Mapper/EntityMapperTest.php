<?php

namespace App\Tests\Infrastructure\Mapper;

use App\Domain\Model\FruitCollection;
use App\Domain\Model\VegetableCollection;
use App\Infrastructure\Entity\Fruit;
use App\Infrastructure\Entity\Vegetable;
use App\Infrastructure\Mapper\EntityMapper;
use App\Tests\Builder\FruitEntityBuilder;
use App\Tests\Builder\FruitModelBuilder;
use App\Tests\Builder\VegetableEntityBuilder;
use App\Tests\Builder\VegetableModelBuilder;
use PHPUnit\Framework\TestCase;

class EntityMapperTest extends TestCase
{
    private EntityMapper $unit;

    protected function setUp(): void
    {
        $this->unit = new EntityMapper();
    }

    public function testMapToFruitEntity(): void
    {
        // Arrange
        $fruitModel = FruitModelBuilder::create()->build();

        // Act
        $fruitEntity = $this->unit->mapToFruitEntity($fruitModel);

        // Assert
        $this->assertInstanceOf(Fruit::class, $fruitEntity);
        $this->assertSame($fruitModel->getId(), $fruitEntity->getId());
        $this->assertSame($fruitModel->getName(), $fruitEntity->getName());
        $this->assertEquals($fruitModel->getQuantity(), $fruitEntity->getQuantity());
        $this->assertSame($fruitModel->getUnit(), $fruitEntity->getUnit());
    }

    public function testMapToVegetableEntity(): void
    {
        // Arrange
        $vegetableModel = VegetableModelBuilder::create()->build();

        // Act
        $vegetableEntity = $this->unit->mapToVegetableEntity($vegetableModel);

        // Assert
        $this->assertInstanceOf(Vegetable::class, $vegetableEntity);
        $this->assertSame($vegetableModel->getId(), $vegetableEntity->getId());
        $this->assertSame($vegetableModel->getName(), $vegetableEntity->getName());
        $this->assertEquals($vegetableModel->getQuantity(), $vegetableEntity->getQuantity());
        $this->assertSame($vegetableModel->getUnit(), $vegetableEntity->getUnit());
    }

    public function testMapToFruitCollection(): void
    {
        // Arrange
        $fruitEntity1 = FruitEntityBuilder::create()->withId(1)->build();
        $fruitEntity2 = FruitEntityBuilder::create()->withId(2)->build();
        $entities = [$fruitEntity1, $fruitEntity2];

        // Act
        $fruitCollection = $this->unit->mapToFruitCollection($entities);

        // Assert
        $this->assertInstanceOf(FruitCollection::class, $fruitCollection);
        $this->assertCount(2, $fruitCollection->list());
        $this->assertSame(1, $fruitCollection->list()[0]->getId());
        $this->assertSame(2, $fruitCollection->list()[1]->getId());
    }

    public function testMapToVegetableCollection(): void
    {
        // Arrange
        $vegetableEntity1 = VegetableEntityBuilder::create()->withId(1)->build();
        $vegetableEntity2 = VegetableEntityBuilder::create()->withId(2)->build();
        $entities = [$vegetableEntity1, $vegetableEntity2];

        // Act
        $vegetableCollection = $this->unit->mapToVegetableCollection($entities);

        // Assert
        $this->assertInstanceOf(VegetableCollection::class, $vegetableCollection);
        $this->assertCount(2, $vegetableCollection->list());
        $this->assertSame(1, $vegetableCollection->list()[0]->getId());
        $this->assertSame(2, $vegetableCollection->list()[1]->getId());
    }
}
