<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory;

use App\Domain\Model\Product\IProductFactory;
use App\Domain\Model\Product\Product;
use App\Domain\Model\Product\ProductTypeEnum;

final class ProductFactory implements IProductFactory
{

    public function create(ProductTypeEnum $type, string $sku, string $name, string $price, int $quantity): Product {
        return new ($type->value)(
            name    : $name,
            SKU     : $sku,
            price   : $price,
            quantity: $quantity,
        );
    }

}
