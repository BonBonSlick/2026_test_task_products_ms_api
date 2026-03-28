<?php

declare(strict_types=1);

namespace App\Domain\Model\Product\Subtype;

use App\Domain\Model\Product\Product;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Pencil extends Product
{

}
