<?php

// src/Controller/Api/ProductController.php

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {}

    /**
     * GET /api/products
     * GET /api/products?category=1
     *
     * Public endpoint — no authentication required.
     * Returns all products (optionally filtered by category ID).
     */
    #[Route('/products', name: 'products_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $categoryId = $request->query->get('category');

        if ($categoryId) {
            $products = $this->productRepository->findBy([
                'category' => (int) $categoryId,
            ]);
        } else {
            $products = $this->productRepository->findAll();
        }

        $data = array_map(function ($product) {
            $imageUrl = null;
            if ($product->getImage()) {
                // Build full image URL — adjust the base URL to match your server
                $imageUrl = 'https://webdevfinalproject-production-a2ea.up.railway.app/uploads/images/' . $product->getImage();
            }

            return [
                'id'          => $product->getId(),
                'name'        => $product->getName(),
                'description' => $product->getDescription(),
                'price'       => $product->getPrice(),
                'image'       => $imageUrl,
                'size'        => $product->getSize(),
                'quantity'    => $product->getQuantity(),
                'category'    => $product->getCategory() ? [
                    'id'   => $product->getCategory()->getId(),
                    'name' => $product->getCategory()->getName(),
                ] : null,
            ];
        }, $products);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/categories
     *
     * Public endpoint — returns all categories for filter tabs.
     */
    #[Route('/categories', name: 'categories_index', methods: ['GET'])]
    public function categories(): JsonResponse
    {
        $categories = $this->categoryRepository->findAll();

        $data = array_map(fn($cat) => [
            'id'          => $cat->getId(),
            'name'        => $cat->getName(),
            'description' => $cat->getDescription(),
        ], $categories);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
