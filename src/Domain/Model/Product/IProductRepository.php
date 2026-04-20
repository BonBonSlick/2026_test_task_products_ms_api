<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

interface IProductRepository
{

    public function findById(string $uuid): ?Product;

    public function decreaseStock(string $uuid, int $quantity): int;

    public function findBy(
        array      $criteria,
        array|null $orderBy = null,
        int|null   $limit = null,
        int|null   $offset = null,
    ): array;

}
