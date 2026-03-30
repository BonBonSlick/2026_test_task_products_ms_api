<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation;

use App\Domain\Model\Product\Product;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields     : ['sku'],
    entityClass: Product::class,
)]
#[OA\Schema]
final readonly class CreateProductDTO
{

    public function __construct(
        #[OA\Property(description: 'The unique identifier of the product.', maxLength: 50, minLength: 2,)]
        #[Assert\Length(
            min: 2,
            max: 50,
        )]
        public string $name,

        #[OA\Property(description: 'The unique SKU of the product.', maxLength: 12, minLength: 2,)]
        #[Assert\Length(
            min: 2,
            max: 12,
        )]
        public string $sku,

        #[OA\Property(description: 'Price of the product.', minimum: 0,)]
        #[Assert\PositiveOrZero]
        public string $price,

        #[OA\Property(description: 'Total products in stock.', minimum: 0,)]
        #[Assert\PositiveOrZero]
        public int    $quantity,
    ) {}

}
