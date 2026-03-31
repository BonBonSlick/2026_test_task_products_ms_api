<?php

declare(strict_types=1);

namespace App\Application\CQRS\Command\Handler;

use App\Application\CQRS\Command\UseCase\CreateProduct;
use App\Domain\Interface\ICommandHandler;
use App\Domain\Model\Product\IProductFactory;
use App\Domain\Model\Product\IProductRepository;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(sign: true)]
final readonly class CreateProductHandler implements ICommandHandler
{

    public function __construct(
        private IProductRepository $productRepository,
        private IProductFactory    $productFactory,
    ) {}

    public function __invoke(CreateProduct $command): void {
        $this->productRepository->save(
            product: $this->productFactory->create(
                       sku     : $command->sku,
                       name    : $command->name,
                       price   : $command->price,
                       quantity: $command->quantity,
                   ),
        );
    }

}
