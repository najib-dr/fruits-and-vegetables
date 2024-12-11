<?php

namespace App\Tests\Builder;

use App\Application\Dto\ItemDto;
use App\Domain\Enum\Type;
use App\Domain\Enum\Unit;

class ItemDtoBuilder
{
    private function __construct(
        private int $id = 1,
        private string $name = 'Avocado',
        private string $type = Type::Fruit->value,
        private int $quantity = 1000,
        private string $unit = Unit::Gram->value,
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

    public function withType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function withQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function withUnit(string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function build(): ItemDto
    {
        return new ItemDto(
            id: $this->id,
            name: $this->name,
            type: $this->type,
            quantity: $this->quantity,
            unit: $this->unit,
        );
    }
}
