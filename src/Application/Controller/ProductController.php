<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\CQRS\Command\UseCase\CreateProduct;
use App\Application\CQRS\Query\UseCase\GetProduct;
use App\Application\CQRS\Query\UseCase\GetProductList;
use App\Domain\Model\Product\IProductRepository;
use App\Domain\Model\Product\Product;
use App\Infrastructure\Validation\CreateProductDTO;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Shared\Contracts\DTO\ProductCreated;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/product', name: 'product.')]
#[OA\Tag(name: 'Product', description: 'Product management')]
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
    #[OA\Post(
        path       : '/product/create',
        summary    : 'Create a new product',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(ref: new Model(type: CreateProductDTO::class)),
        ),
        tags       : ['Product'],
        responses  : [
            new OA\Response(
                response   : 201,
                description: 'Product created',
                content    : new OA\JsonContent(ref: new Model(type: Product::class)),
            ),
        ]
    )]
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateProductDTO $dto): JsonResponse {
        $this->commandBus->dispatch(
            new CreateProduct(
                sku     : $dto->sku,
                name    : $dto->name,
                price   : $dto->price,
                quantity: $dto->quantity,
            ),
        );

        /** @var Product $product */
        $product = $this->queryBus
                       ->dispatch(message: new GetProduct(name: $dto->name, sku: $dto->sku,))
                       ->last(HandledStamp::class)
                       ->getResult()[0];

        $this->rmqBus->dispatch(
            message: new ProductCreated(
                         id      : $product->id()->toRfc4122(),
                         name    : $product->name(),
                         sku     : $product->sku(),
                         price   : $product->price(),
                         quantity: $product->quantity(),
                     ),
        );

        return $this->json(
            data   : $product,
            status : Response::HTTP_CREATED,
            context: ['groups' => ['*']],
        );
    }

    /**
     * @throws ExceptionInterface
     */
    #[OA\Get(
        path     : '/product/list',
        summary  : 'Get list of products',
        tags     : ['Product'],
        responses: [
            new OA\Response(
                response   : 200,
                description: 'List of products',
                content    : new OA\JsonContent(
                                 type : 'array',
                                 items: new OA\Items(ref: new Model(type: Product::class, groups: ['main'])),
                             ),
            ),
        ]
    )]
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse {
        return $this->json(
            data   : $this->queryBus
                         ->dispatch(new GetProductList())
                         ->last(HandledStamp::class)
                         ->getResult(),
            context: ['groups' => ['main']],
        );
    }

    #[OA\Get(
        path      : '/product/{id}',
        summary   : 'Get product by ID',
        tags      : ['Product'],
        parameters: [
            new OA\Parameter(
                name    : 'id',
                in      : 'path',
                required: true,
                schema  : new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses : [
            new OA\Response(
                response   : 200,
                description: 'Product details',
                content    : new OA\JsonContent(ref: new Model(type: Product::class)),
            ),
        ]
    )]
    #[Route('/{id}', name: 'info', methods: ['GET'])]
    public function show(Product $product): JsonResponse {
        return $this->json(
            data   : $product,
            context: ['groups' => ['main', 'info']],
        );
    }

}
