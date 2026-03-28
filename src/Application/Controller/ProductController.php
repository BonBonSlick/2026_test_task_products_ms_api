<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\CQRS\Command\UseCase\CreateProduct;
use App\Application\CQRS\Query\UseCase\GetProduct;
use App\Application\CQRS\Query\UseCase\GetProductList;
use App\Domain\Model\Product\Event\ProductCreated;
use App\Domain\Model\Product\IProductRepository;
use App\Domain\Model\Product\Product;
use App\Domain\Model\Product\ProductTypeEnum;
use App\Infrastructure\Validation\CreateProductDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route(path: '/product', name: 'product.')]
final class ProductController extends AbstractController
{

    public function __construct(
        protected readonly IProductRepository  $productRepository,
        protected readonly MessageBusInterface $commandBus,
        protected readonly MessageBusInterface $queryBus,
        protected readonly MessageBusInterface $rmqBus,
    ) {}

    /**
     * @throws ExceptionInterface
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateProductDTO $dto): JsonResponse {
        $this->commandBus->dispatch(
            new CreateProduct(
                type    : ProductTypeEnum::pen,
                sku     : $dto->SKU,
                name    : $dto->name,
                price   : $dto->price,
                quantity: $dto->quantity,
            ),
        );

        /** @var Product $product */
        $product = $this->queryBus
            ->dispatch(message: new GetProduct(name: $dto->name, sku: $dto->SKU,))
            ->last(HandledStamp::class)
            ->getResult()[0]
        ;

        $this->rmqBus->dispatch(
            message: new ProductCreated(
                         id      : $product->getId(),
                         name    : $product->getName(),
                         price   : $product->getPrice(),
                         quantity: $product->getQuantity(),
                     )
        );

        return $this->json(data: $product, status: Response::HTTP_CREATED);
    }

    #[Route('', name: 'product_list', methods: ['GET'])]
    public function list(): JsonResponse {
        // Dispatch GetProductList query through the CQRS bus
        $envelope = $this->queryBus->dispatch(new GetProductList());

        // Get the products from the handled stamp
        $handledStamp = $envelope->last(HandledStamp::class);
        $products     = $handledStamp->getResult();

        return $this->json(data: $handledStamp->getResult());
    }

    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function show(string $id): JsonResponse {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException) {
            return new JsonResponse(['error' => 'Invalid UUID'], Response::HTTP_BAD_REQUEST);
        }

        $product = $this->productRepository->findById($uuid->toRfc4122());

        if ( ! $product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(
            [
                'id'       => $product->getId()->toString(),
                'name'     => $product->getName(),
                'price'    => $product->getPrice(),
                'quantity' => $product->getQuantity(),
            ],
        );
    }

}
