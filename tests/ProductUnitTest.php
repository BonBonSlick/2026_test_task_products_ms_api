<?php

namespace App\Tests;

use App\Domain\Model\Product\Product;
use App\Infrastructure\Factory\ProductFactory;
use PHPUnit\Framework\TestCase;

class ProductUnitTest extends TestCase
{

    public function testFactoryCreatesDifferentTypeProducts(): void {
        $factory = new ProductFactory();
        self::assertInstanceOf(
            expected: Product::class,
            actual  : $factory->create(
                          sku     : 'test',
                          name    : 'test',
                          price   : '10.00',
                          quantity: 1,
                      ),
        );
    }

}
