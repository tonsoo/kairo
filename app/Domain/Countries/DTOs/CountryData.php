<?php

namespace App\Domain\Countries\DTOs;

final readonly class CountryData
{
    public function __construct(
        public string $name,
        public string $code,
    ) {}
}
