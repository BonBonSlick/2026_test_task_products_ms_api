<?php

declare(strict_types=1);

namespace App\Application\CQRS\Command\Handler;

use App\Domain\Interface\ICommandHandler;
use App\Domain\Model\Product\IProductRepository;
use Shared\Contracts\DTO\OrderCreated;
use Shared\Contracts\DTO\Product\ProductOutOfStock;
use Shared\Contracts\DTO\Product\ProductQuantityDecreased;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class OrderCreatedHandler implements ICommandHandler
{

    public function __construct(private IProductRepository $productRepository, private MessageBusInterface $eventBus) {}

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(OrderCreated $dto): void {
        $productId               = $dto->productID;
        $isProductStockDecreased = (bool)$this->productRepository->decreaseStock($productId, $dto->quantity);

        if (true === $isProductStockDecreased) {
            $product = $this->productRepository->findById($dto->productID);
            $event   = new ProductQuantityDecreased(
                productID      : $productId,
                orderID        : $dto->orderID,
                updatedQuantity: $product->quantity(),
            );
        } else {
            $event = new ProductOutOfStock(
                productID: $productId,
                orderID  : $dto->orderID,
            );
        }

        $this->eventBus->dispatch(message: $event);
    }

}
