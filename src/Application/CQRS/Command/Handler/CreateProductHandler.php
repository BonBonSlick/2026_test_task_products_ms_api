<?php

declare(strict_types=1);

namespace App\Application\CQRS\Command\Handler;

use App\Application\CQRS\Command\UseCase\CreateProduct;
use App\Domain\Model\Product\IProductFactory;
use App\Domain\Model\Product\IProductRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateProductHandler
{

    public function __construct(
        private IProductRepository $productRepository,
        private IProductFactory    $productFactory,
    ) {}

    public function __invoke(CreateProduct $command): void {
        $this->productRepository->save(
            product: $this->productFactory->create(
                       type    : $command->getType(),
                       sku     : $command->getSku(),
                       name    : $command->getName(),
                       price   : $command->getPrice(),
                       quantity: $command->getQuantity(),
                   ),
        );
    }

}
