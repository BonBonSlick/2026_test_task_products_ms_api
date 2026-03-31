<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use App\Infrastructure\Persistence\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Shared\Contracts\Model\AbstractProduct;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[OA\Schema]
#[ORM\Table(name: 'products')]
class Product extends AbstractProduct
{

    public function updateName(string $name): void {
        $this->name = $name;
    }

    public function updatePrice(string $price): void {
        $this->price = $price;
    }

    public function increaseQuantity(int $quantity): void {
        $this->quantity += $quantity;
    }

}
