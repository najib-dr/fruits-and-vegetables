<?php

namespace App\Domain\Model;

use App\Domain\Converter\UnitConverter;
use App\Domain\Enum\Type;
use App\Domain\Enum\Unit;

abstract class AbstractProduct
{
    protected Type $type;

    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private float $quantity,
        private Unit $unit,
    ) {
        $this->setQuantityInGrams();
        $this->setType();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getUnit(): Unit
    {
        return $this->unit;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function convertUnitToKilogram(): void
    {
        $this->quantity = UnitConverter::gramToKilogram($this->quantity);
        $this->unit = Unit::Kilogram;
    }

    private function setQuantityInGrams(): void
    {
        if (Unit::Gram !== $this->unit) {
            $this->quantity = UnitConverter::toGrams($this->quantity, $this->unit);
            $this->unit = Unit::Gram;
        }
    }

    abstract protected function setType(): void;
}
