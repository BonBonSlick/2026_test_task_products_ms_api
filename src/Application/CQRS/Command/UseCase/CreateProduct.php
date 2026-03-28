<?php

declare(strict_types=1);

namespace App\Application\CQRS\Command\UseCase;

use App\Domain\Model\Product\ProductTypeEnum;

final readonly class CreateProduct
{

    public function __construct(
        private ProductTypeEnum $type,
        private string          $sku,
        private string          $name,
        private string          $price,
        private int             $quantity,
    ) {}

    public function getType(): ProductTypeEnum {
        return $this->type;
    }

    public function getSku(): string {
        return $this->sku;
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
