<?php

declare(strict_types=1);

namespace App\Application\CQRS\Query\Handler;

use App\Application\CQRS\Query\UseCase\GetProductList;
use App\Domain\Interface\IQueryHandler;
use App\Domain\Model\Product\IProductRepository;
use App\Domain\Model\Product\Product;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProductListHandler implements IQueryHandler
{

    public function __construct(private IProductRepository $productRepository) {}

    /**
     * @return Product[]
     */
    public function __invoke(GetProductList $query): array {
        return $this->productRepository->findAllProducts();
    }

}
