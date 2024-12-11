<?php

namespace App\Application\Adapter\DataGateway;

use App\Domain\Model\Vegetable;
use App\Domain\Model\VegetableCollection;

interface VegetableDataGatewayInterface
{
    public function batchSave(VegetableCollection $vegetableCollection): void;

    public function save(Vegetable $vegetableModel): void;

    public function getVegetableCollection(string $nameFilter): VegetableCollection;

    /**
     * @param int[] $vegetableIds
     *
     * @return int[]
     */
    public function getExistingIds(array $vegetableIds): array;
}
