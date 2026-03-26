<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Domain\Model\Product;
use App\Infrastructure\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/products')]
final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    #[Route('', name: 'product_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['price'], $data['quantity'])) {
            return new JsonResponse(['error' => 'Missing parameters'], Response::HTTP_BAD_REQUEST);
        }

        $product = new Product(
            Uuid::v4(),
            $data['name'],
            (float) $data['price'],
            (int) $data['quantity']
        );

        $this->productRepository->save($product);

        // TODO: Publish to RabbitMQ

        return new JsonResponse([
            'id' => $product->getId()->toString(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'quantity' => $product->getQuantity(),
        ], Response::HTTP_CREATED);
    }

    #[Route('', name: 'product_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $products = $this->productRepository->findAllProducts();
        $data = array_map(fn (Product $p) => [
            'id' => $p->getId()->toString(),
            'name' => $p->getName(),
            'price' => $p->getPrice(),
            'quantity' => $p->getQuantity(),
        ], $products);

        return new JsonResponse($data);
    }

    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException) {
            return new JsonResponse(['error' => 'Invalid UUID'], Response::HTTP_BAD_REQUEST);
        }

        $product = $this->productRepository->findById($uuid);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $product->getId()->toString(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'quantity' => $product->getQuantity(),
        ]);
    }
}
