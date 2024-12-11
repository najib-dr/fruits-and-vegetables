<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Model\Fruit as FruitModel;
use App\Domain\Model\FruitCollection;
use App\Domain\Model\Vegetable as VegetableModel;
use App\Domain\Model\VegetableCollection;
use App\Infrastructure\Entity\Fruit as FruitEntity;
use App\Infrastructure\Entity\Vegetable as VegetableEntity;

class EntityMapper
{
    public function mapToFruitEntity(FruitModel $fruitModel): FruitEntity
    {
        $entity = new FruitEntity();

        return $entity
            ->setId($fruitModel->getId())
            ->setName($fruitModel->getName())
            ->setQuantity((int) $fruitModel->getQuantity())
            ->setUnit($fruitModel->getUnit());
    }

    /**
     * @param FruitEntity[] $entities
     */
    public function mapToFruitCollection(array $entities): FruitCollection
    {
        $collection = new FruitCollection();

        foreach ($entities as $entity) {
            $collection->add($this->mapToFruitModel($entity));
        }

        return $collection;
    }

    public function mapToVegetableEntity(VegetableModel $vegetableModel): VegetableEntity
    {
        $entity = new VegetableEntity();

        return $entity
            ->setId($vegetableModel->getId())
            ->setName($vegetableModel->getName())
            ->setQuantity((int) $vegetableModel->getQuantity())
            ->setUnit($vegetableModel->getUnit());
    }

    /**
     * @param VegetableEntity[] $entities
     */
    public function mapToVegetableCollection(array $entities): VegetableCollection
    {
        $collection = new VegetableCollection();

        foreach ($entities as $entity) {
            $collection->add($this->mapToVegetableModel($entity));
        }

        return $collection;
    }

    private function mapToFruitModel(FruitEntity $fruitEntity): FruitModel
    {
        return new FruitModel(
            $fruitEntity->getId(),
            $fruitEntity->getName(),
            $fruitEntity->getQuantity(),
            $fruitEntity->getUnit(),
        );
    }

    private function mapToVegetableModel(VegetableEntity $vegetableEntity): VegetableModel
    {
        return new VegetableModel(
            $vegetableEntity->getId(),
            $vegetableEntity->getName(),
            $vegetableEntity->getQuantity(),
            $vegetableEntity->getUnit(),
        );
    }
}
