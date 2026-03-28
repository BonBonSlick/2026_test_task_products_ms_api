<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use App\Domain\Model\Product\Subtype\Pen;
use App\Domain\Model\Product\Subtype\Pencil;

enum ProductTypeEnum: string
{

    case pen    = Pen::class;
    case pencil = Pencil::class;

}
