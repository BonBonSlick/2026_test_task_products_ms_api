<?php

declare(strict_types=1);

namespace App\Application\CQRS\Command\Handler;

use Shared\Contracts\DTO\OrderCreated;
use Shared\Contracts\DTO\ProductQuantityDecreased;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class OrderCreatedHandler
{

    public function __construct(private readonly MessageBusInterface $eventBus) {}

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(OrderCreated $dto): void {
        $test = 123;
        $this->eventBus->dispatch(message: new ProductQuantityDecreased(productID: $productID, quantity: $quantity));
    }

}
