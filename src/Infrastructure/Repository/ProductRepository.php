<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Model\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Product>
 */
final class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $product): void
    {
        $this->getEntityManager()->persist($product);
        $this->getEntityManager()->flush();
    }

    public function findById(Uuid $id): ?Product
    {
        return $this->find($id);
    }

    /**
     * @return Product[]
     */
    public function findAllProducts(): array
    {
        return $this->findAll();
    }
}
