<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory;

use App\Domain\Model\Product\IProductFactory;
use App\Domain\Model\Product\Product;

final class ProductFactory implements IProductFactory
{

    public function create(string $sku, string $name, string $price, int $quantity): Product {
        return new Product(
            name    : $name,
            sku     : $sku,
            price   : $price,
            quantity: $quantity,
        );
    }

}
