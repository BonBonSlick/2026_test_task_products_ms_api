<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Model\Product\IProductRepository;
use App\Domain\Model\Product\Product;

/**
 * Used for testing to avoid database interactions.
 */
final class InMemoryProductRepository implements IProductRepository
{

    private array $products = [];

    public function findById(string $uuid): ?Product {
        return $this->products[$uuid] ?? null;
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array {
        $filtered = array_filter(
            array   : $this->products,
            callback: static function (Product $product) use ($criteria): bool {
                foreach ($criteria as $getterName => $value) {
                    $actualValue =
                        method_exists($product, $getterName)
                            ? $product->$getterName()
                            : ($product->$getterName ?? null);

                    if ($actualValue !== $value) {
                        return false;
                    }
                }

                return true;
            },
        );

        return array_values($filtered);
    }

    public function findAllProducts(): array {
        return $this->products;
    }

    public function save(Product $product): void {
        $this->products[$product->id()->toRfc4122()] = $product;
    }

}
