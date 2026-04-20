<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Model\Product\IProductRepository;
use App\Domain\Model\Product\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function decreaseStock(string $uuid, int $quantity): int {
        $manager      = $this->getEntityManager();
        $affectedRows = $manager
            ->createQuery(
                'UPDATE ' . Product::class . ' product
                     SET product.quantity = product.quantity - :quantity
                     WHERE product.id = :uuid
                        AND product.quantity >= :quantity',
            )
            ->setParameter('uuid', $uuid, 'uuid')
            ->setParameter('quantity', $quantity)
            ->execute()
        ;

        $manager->clear();

        return (int)$affectedRows;
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
