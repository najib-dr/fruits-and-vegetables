<?php

namespace App\Tests\Builder;

use App\Domain\Enum\Unit;
use App\Domain\Model\Fruit;

class FruitModelBuilder
{
    private function __construct(
        private int $id = 1,
        private string $name = 'Apple',
        private float $quantity = 2000,
        private Unit $unit = Unit::Gram,
    ) {
    }

    public static function create(): self
    {
        return new self();
    }

    public function withId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function withUnit(Unit $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function build(): Fruit
    {
        return new Fruit(
            $this->id,
            $this->name,
            $this->quantity,
            $this->unit,
        );
    }
}
