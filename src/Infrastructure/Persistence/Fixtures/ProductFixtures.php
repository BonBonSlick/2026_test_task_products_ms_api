<?php

namespace App\Infrastructure\Persistence\Fixtures;

use App\Domain\Model\Product\IProductFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Random\RandomException;
use Random\Randomizer;

use function random_int;
use function round;

class ProductFixtures extends Fixture
{

    public function __construct(private readonly IProductFactory $productFactory) {}

    /**
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void {
        $randomizer = new Randomizer();
        for ($iteration = 0; $iteration < 50; $iteration++) {
            $manager->persist(
                object: $this->productFactory->create(
                          sku     : 'sku' . $iteration,
                          name    : 'product ' . $iteration,
                          price   : (string)round(10 + $randomizer->nextFloat() * (1000 - 10), 2),
                          quantity: random_int(0, 100),
                      ),
            );

            if (0 === ($iteration % 5)) {
                $manager->flush();
            }
        }

        $manager->flush();
    }

}
