<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation;

use App\Domain\Model\Product\Product;
use App\Domain\Model\Product\ProductTypeEnum;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields     : ['SKU'],
    entityClass: Product::class,
)]
final readonly class CreateProductDTO
{

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(choices: [ProductTypeEnum::pencil->name, ProductTypeEnum::pen->name])]
        public string $type,

        #[Assert\NotBlank]
        #[Assert\Length(
            min: 2,
            max: 50,
        )]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Length(
            min: 2,
            max: 12,
        )]
        public string $SKU,

        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public string $price,

        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public int    $quantity,
    ) {}

}
