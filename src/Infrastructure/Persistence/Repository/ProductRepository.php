<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Model\Product\IProductRepository;
use App\Domain\Model\Product\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Product>
 */
final class ProductRepository extends ServiceEntityRepository implements IProductRepository
{

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $product): void {
        $manager = $this->getEntityManager();
        $manager->persist($product);
        $manager->flush();
    }

    public function findById(string $id): ?Product {
        return $this->find($id);
    }

    /**
     * @return Product[]
     */
    public function findAllProducts(): array {
        return $this->findAll();
    }
}
