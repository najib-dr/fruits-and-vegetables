<?php

namespace App\Domain\Model;

class FruitCollection
{
    /** @var Fruit[] */
    private array $fruits = [];

    public function add(Fruit $fruit): void
    {
        $this->fruits[] = $fruit;
    }

    public function remove(int $id): void
    {
        $this->fruits = array_filter($this->fruits, fn (Fruit $fruit) => $fruit->getId() !== $id);
    }

    /** @return Fruit[] */
    public function list(): array
    {
        return $this->fruits;
    }

    public function count(): int
    {
        return count($this->fruits);
    }

    public function search(string $name): self
    {
        $filtered = array_filter($this->fruits, function (Fruit $fruit) use ($name) {
            return !$name || false !== stripos($fruit->getName(), $name);
        });

        $filteredCollection = new self();
        foreach ($filtered as $fruit) {
            $filteredCollection->add($fruit);
        }

        return $filteredCollection;
    }
}
