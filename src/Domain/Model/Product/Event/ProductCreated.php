<?php

declare(strict_types=1);

namespace App\Domain\Model\Product\Event;

use Symfony\Component\Uid\Uuid;

final class ProductCreated
{

    public function __construct(
        private readonly Uuid   $id,
        private readonly string $name,
        private readonly string $price,
        private readonly int    $quantity,
    ) {}

    public function getId(): Uuid {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPrice(): string {
        return $this->price;
    }

    public function getQuantity(): int {
        return $this->quantity;
    }

}
