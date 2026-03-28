<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use App\Domain\Model\Product\Subtype\Pen;
use App\Domain\Model\Product\Subtype\Pencil;
use App\Infrastructure\Persistence\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Index(name: 'sku_idx', columns: ['sku'])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[ORM\DiscriminatorMap([
    'pen'    => Pen::class,
    'pencil' => Pencil::class,
])]
abstract class Product
{

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[Groups(['create', 'list', 'info'])]
    private readonly Uuid $id;

    public function __construct(
        #[Groups(['create', 'list', 'info'])]
        #[ORM\Column(length: 255)]
        private string $name,

        #[Groups(['create', 'info'])]
        #[ORM\Column(length: 12, unique: true)]
        private string $SKU,

        #[Groups(['create', 'list', 'info'])]
        #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
        private string $price,

        #[Groups(['create', 'info'])]
        #[ORM\Column]
        private int    $quantity,
    ) {
        $this->id = Uuid::v4();
    }

    public function getId(): Uuid {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getPrice(): string {
        return $this->price;
    }

    public function setPrice(string $price): void {
        $this->price = $price;
    }

    public function getQuantity(): int {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void {
        $this->quantity = $quantity;
    }

    public function getSKU(): string {
        return $this->SKU;
    }

    public function setSKU(string $SKU): void {
        $this->SKU = $SKU;
    }

}
