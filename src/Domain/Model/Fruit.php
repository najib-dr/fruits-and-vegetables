<?php

namespace App\Domain\Model;

use App\Domain\Enum\Type;

class Fruit extends AbstractProduct
{
    protected function setType(): void
    {
        $this->type = Type::Fruit;
    }
}
