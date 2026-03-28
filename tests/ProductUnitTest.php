<?php

namespace App\Tests;

use App\Domain\Model\Product\ProductTypeEnum;
use App\Domain\Model\Product\Subtype\Pen;
use App\Domain\Model\Product\Subtype\Pencil;
use App\Infrastructure\Factory\ProductFactory;
use PHPUnit\Framework\TestCase;

class ProductUnitTest extends TestCase
{

    public function testFactoryCreatesDifferentTypeProducts(): void {
        $factory = new ProductFactory();
        self::assertInstanceOf(
            expected: Pen::class,
            actual  : $factory->create(
                          type    : ProductTypeEnum::pen,
                          sku     : 'test',
                          name    : 'test',
                          price   : '10.00',
                          quantity: 1,
                      ),
        );
        self::assertInstanceOf(
            expected: Pencil::class,
            actual  : $factory->create(
                          type    : ProductTypeEnum::pencil,
                          sku     : 'test',
                          name    : 'test',
                          price   : '10.00',
                          quantity: 1,
                      ),
        );
    }

}
