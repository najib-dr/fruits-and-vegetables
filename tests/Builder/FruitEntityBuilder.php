<?php

namespace App\Tests\Builder;

use App\Domain\Enum\Unit;
use App\Infrastructure\Entity\Fruit;

class FruitEntityBuilder
{
    private function __construct(
        private int $id = 1,
        private string $name = 'Apple',
        private int $quantity = 2000,
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

    public function withQuantity(int $quantity): self
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
        return (new Fruit())
            ->setId($this->id)
            ->setName($this->name)
            ->setQuantity($this->quantity)
            ->setUnit($this->unit);
    }
}
