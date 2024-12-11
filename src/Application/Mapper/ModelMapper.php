<?php

namespace App\Application\Mapper;

use App\Application\Dto\ItemDto;
use App\Domain\Enum\Unit;
use App\Domain\Model\Fruit;
use App\Domain\Model\Vegetable;

class ModelMapper
{
    public function mapToFruit(ItemDto $item): Fruit
    {
        return new Fruit(
            $item->id,
            $item->name,
            $item->quantity,
            Unit::from($item->unit),
        );
    }

    public function mapToVegetable(ItemDto $item): Vegetable
    {
        return new Vegetable(
            $item->id,
            $item->name,
            $item->quantity,
            Unit::from($item->unit),
        );
    }
}
