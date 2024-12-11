<?php

namespace App\Application\Adapter\DataGateway;

use App\Domain\Model\Fruit;
use App\Domain\Model\FruitCollection;

interface FruitDataGatewayInterface
{
    public function batchSave(FruitCollection $fruitCollection): void;

    public function save(Fruit $fruitModel): void;

    public function getFruitCollection(string $nameFilter): FruitCollection;

    /**
     * @param int[] $fruitIds
     *
     * @return int[]
     */
    public function getExistingIds(array $fruitIds): array;
}
