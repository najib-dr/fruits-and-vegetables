<?php

namespace App\Application\Service;

use App\Application\Adapter\DataGateway\FruitDataGatewayInterface;
use App\Application\Adapter\DataGateway\VegetableDataGatewayInterface;
use App\Application\Dto\ItemDto;
use App\Application\Dto\QueryParamsDto;
use App\Application\Mapper\ModelMapper;
use App\Domain\Enum\Type;
use App\Domain\Enum\Unit;
use App\Domain\Model\FruitCollection;
use App\Domain\Model\VegetableCollection;
use PHPUnit\Util\Exception;

class StorageService
{
    public function __construct(
        private readonly FruitDataGatewayInterface $fruitDataGateway,
        private readonly VegetableDataGatewayInterface $vegetableDataGateway,
        private readonly ModelMapper $mapper,
    ) {
    }

    /** @param ItemDto[] $items */
    public function storeItems(array $items): void
    {
        $fruitCollection = new FruitCollection();
        $vegetableCollection = new VegetableCollection();

        foreach ($items as $item) {
            match ($item->type) {
                Type::Fruit->value => $fruitCollection->add($this->mapper->mapToFruit($item)),
                Type::Vegetable->value => $vegetableCollection->add($this->mapper->mapToVegetable($item)),
                default => throw new Exception('Unsupported type.'),
            };
        }

        if ($fruitCollection->count() > 0) {
            $this->fruitDataGateway->batchSave($fruitCollection);
        }

        if ($vegetableCollection->count() > 0) {
            $this->vegetableDataGateway->batchSave($vegetableCollection);
        }
    }

    public function storeItem(ItemDto $item): void
    {
        match ($item->type) {
            Type::Fruit->value => $this->fruitDataGateway->save($this->mapper->mapToFruit($item)),
            Type::Vegetable->value => $this->vegetableDataGateway->save($this->mapper->mapToVegetable($item)),
            default => throw new Exception('Unsupported type.'),
        };
    }

    /**
     * @param ItemDto[] $items
     *
     * @return int[]
     */
    public function existingIds(array $items): array
    {
        $fruitIds = [];
        $vegetableIds = [];
        $existingFruitIds = [];
        $existingVegetableIds = [];

        foreach ($items as $item) {
            match ($item->type) {
                Type::Fruit->value => $fruitIds[] = $item->id,
                Type::Vegetable->value => $vegetableIds[] = $item->id,
                default => throw new Exception('Unsupported type.'),
            };
        }

        if (count($fruitIds) > 0) {
            $existingFruitIds = $this->fruitDataGateway->getExistingIds($fruitIds);
        }

        if (count($vegetableIds) > 0) {
            $existingVegetableIds = $this->vegetableDataGateway->getExistingIds($vegetableIds);
        }

        return array_merge($existingFruitIds, $existingVegetableIds);
    }

    public function itemExists(mixed $item): bool
    {
        $fruitIds = [];
        $vegetableIds = [];

        match ($item->type) {
            Type::Fruit->value => $fruitIds = $this->fruitDataGateway->getExistingIds([$item->id]),
            Type::Vegetable->value => $vegetableIds = $this->vegetableDataGateway->getExistingIds([$item->id]),
            default => throw new Exception('Unsupported type.'),
        };

        return count($fruitIds) > 0 || count($vegetableIds) > 0;
    }

    public function getFruitCollection(QueryParamsDto $queryParamsDto): FruitCollection
    {
        $fruitCollection = $this->fruitDataGateway->getFruitCollection($queryParamsDto->name);

        if ($queryParamsDto->unit === Unit::Kilogram->value) {
            foreach ($fruitCollection->list() as $fruit) {
                $fruit->convertUnitToKilogram();
            }
        }

        return $fruitCollection;
    }

    public function getVegetableCollection(QueryParamsDto $queryParamsDto): VegetableCollection
    {
        $vegetableCollection = $this->vegetableDataGateway->getVegetableCollection($queryParamsDto->name);

        if ($queryParamsDto->unit === Unit::Kilogram->value) {
            foreach ($vegetableCollection->list() as $vegetable) {
                $vegetable->convertUnitToKilogram();
            }
        }

        return $vegetableCollection;
    }
}
