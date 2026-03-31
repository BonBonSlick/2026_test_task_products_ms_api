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
        $product = $this->productRepository->findById($dto->productID);

        if ($product->quantity() >= $dto->quantity) {
            $event = new ProductQuantityDecreased(
                productID      : $product->id()->toRfc4122(),
                orderID        : $dto->orderID,
                updatedQuantity: $product->quantity(),
            );

            $product->decreaseQuantity($dto->quantity);
        } else {
            $event = new ProductOutOfStock(
                productID: $product->id()->toRfc4122(),
                orderID  : $dto->orderID,
            );
        }

        $this->productRepository->save($product);

        $this->eventBus->dispatch(message: $event);
    }

}
