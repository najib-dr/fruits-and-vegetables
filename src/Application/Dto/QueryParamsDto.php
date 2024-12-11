<?php

namespace App\Application\Dto;

readonly class QueryParamsDto
{
    public function __construct(
        public string $name,
        public string $unit,
    ) {
    }
}
