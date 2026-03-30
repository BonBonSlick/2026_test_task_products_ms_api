<?php

declare(strict_types=1);

namespace App\Application\CQRS\Command\UseCase;

final readonly class CreateProduct
{

    public function __construct(
        public string $sku,
        public string $name,
        public string $price,
        public int    $quantity,
    ) {}

}
