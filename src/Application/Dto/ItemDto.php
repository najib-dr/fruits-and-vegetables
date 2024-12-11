<?php

namespace App\Application\Dto;

use App\Domain\Enum\Type;
use App\Domain\Enum\Unit;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ItemDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $id,

        #[Assert\NotBlank]
        #[Assert\Length(max: 25)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Choice(callback: [Type::class, 'values'])]
        public string $type,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $quantity,

        #[Assert\NotBlank]
        #[Assert\Choice(callback: [Unit::class, 'values'])]
        public string $unit,
    ) {
    }
}
