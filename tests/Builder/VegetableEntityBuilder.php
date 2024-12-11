<?php

namespace App\Tests\Builder;

use App\Domain\Enum\Unit;
use App\Infrastructure\Entity\Vegetable;

class VegetableEntityBuilder
{
    private function __construct(
        private int $id = 1,
        private string $name = 'Carrot',
        private int $quantity = 3000,
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

    public function build(): Vegetable
    {
        return (new Vegetable())
            ->setId($this->id)
            ->setName($this->name)
            ->setQuantity($this->quantity)
            ->setUnit($this->unit);
    }
}
