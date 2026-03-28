<?php

declare(strict_types=1);

namespace App\Application\CQRS\Query\UseCase;

final readonly class GetProduct
{

    public function __construct(
        public null|string $id = null,
        public null|string $name = null,
        public null|string $sku = null,
    ) {}

}
