<?php

namespace App\Domain\Model;

class VegetableCollection
{
    /** @var Vegetable[] */
    private array $vegetables = [];

    public function add(Vegetable $vegetable): void
    {
        $this->vegetables[] = $vegetable;
    }

    public function remove(int $id): void
    {
        $this->vegetables = array_filter($this->vegetables, fn (Vegetable $vegetable) => $vegetable->getId() !== $id);
    }

    /** @return Vegetable[] */
    public function list(): array
    {
        return $this->vegetables;
    }

    public function count(): int
    {
        return count($this->vegetables);
    }

    public function search(?string $name): self
    {
        $filtered = array_filter($this->vegetables, function (Vegetable $vegetable) use ($name) {
            return !$name || false !== stripos($vegetable->getName(), $name);
        });

        $filteredCollection = new self();
        foreach ($filtered as $vegetable) {
            $filteredCollection->add($vegetable);
        }

        return $filteredCollection;
    }
}
