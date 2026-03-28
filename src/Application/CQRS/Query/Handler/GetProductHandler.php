<?php

declare(strict_types=1);

namespace App\Application\CQRS\Query\Handler;

use App\Application\CQRS\Query\UseCase\GetProduct;
use App\Domain\Model\Product\IProductRepository;
use App\Domain\Model\Product\Product;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProductHandler
{

    public function __construct(private IProductRepository $productRepository) {}

    /**
     * @return Product[]
     */
    public function __invoke(GetProduct $query): array {
        $criteria = [];
        if (null !== $query->id) {
            $criteria['id'] = $query->id;
        }
        if (null !== $query->name) {
            $criteria['name'] = $query->name;
        }
        if (null !== $query->sku) {
            $criteria['SKU'] = $query->sku;
        }

        return $this->productRepository->findBy(
            criteria: $criteria,
        );
    }

}
