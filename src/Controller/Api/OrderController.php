<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Sales;
use App\Entity\Stock;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use App\Service\NotificationService;
use App\Service\PusherService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_orders_')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository        $orderRepository,
        private readonly ProductRepository      $productRepository,
        private readonly CustomerRepository     $customerRepository,
        private readonly StockRepository        $stockRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface        $logger,
        private readonly PusherService          $pusher,
        private readonly NotificationService    $notifications,
    ) {}

    // =========================================================================
    // GET /api/orders/all  — Staff/Admin only: see ALL customer orders
    // =========================================================================
    #[Route('/orders/all', name: 'list_all', methods: ['GET'])]
    #[IsGranted('ROLE_STAFF')]
    public function listAll(Request $request): JsonResponse
    {
        try {
            $status = $request->query->get('status'); // optional ?status=Pending

            $criteria = [];
            if ($status) $criteria['status'] = $status;

            $orders = $this->orderRepository->findBy(
                $criteria,
                ['createdAt' => 'DESC']
            );

            return $this->json([
                'status' => 'success',
                'orders' => array_map(fn(Order $o) => $this->formatOrder($o), $orders),
                'total'  => count($orders),
            ]);

        } catch (\Exception $e) {
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // =========================================================================
    // PATCH /api/orders/{id}/status  — Staff/Admin: update order status
    // =========================================================================
    #[Route('/orders/{id}/status', name: 'update_status', methods: ['PATCH'])]
    #[IsGranted('ROLE_STAFF')]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $allowed = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Delivered', 'Cancelled', 'Processing', 'Completed'];

        try {
            $order = $this->orderRepository->find($id);
            if (!$order) {
                return $this->json(['status' => 'error', 'message' => 'Order not found.'], JsonResponse::HTTP_NOT_FOUND);
            }

            $data   = json_decode($request->getContent(), true);
            $status = $data['status'] ?? null;

            if (!$status || !in_array($status, $allowed)) {
                return $this->json([
                    'status'  => 'error',
                    'message' => 'Invalid status. Allowed: ' . implode(', ', $allowed),
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $order->setStatus($status);
            $this->entityManager->flush();

            $formattedOrder = $this->formatOrder($order);

            try {
                $this->pusher->orderStatusUpdated($formattedOrder);
            } catch (\Exception $e) {
                $this->logger->warning('Pusher notification failed', ['error' => $e->getMessage()]);
            }

            // FCM push notification → notify the customer their order status changed
            try {
                $orderUser = $order->getCreatedBy();
                if ($orderUser) {
                    $this->notifications->sendToUser(
                        $orderUser,
                        'Order Status Updated',
                        "Your order #{$id} is now: {$status}",
                        ['type' => 'order_status', 'orderId' => (string) $id, 'status' => $status]
                    );
                }
            } catch (\Exception $e) {
                $this->logger->warning('FCM notification failed', ['error' => $e->getMessage()]);
            }

            return $this->json([
                'status'  => 'success',
                'message' => "Order #{$id} status updated to {$status}.",
                'order'   => $formattedOrder,
            ]);

        } catch (\Exception $e) {
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // =========================================================================
    // POST /api/orders
    // =========================================================================
    #[Route('/orders', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['status' => 'error', 'message' => 'Invalid JSON body.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $errors = [];
        if (empty($data['product_id']))    $errors[] = 'product_id is required.';
        if (empty($data['quantity']))      $errors[] = 'quantity is required.';
        if (empty($data['customer_name'])) $errors[] = 'customer_name is required.';

        if (!empty($errors)) {
            return $this->json(['status' => 'error', 'message' => 'Validation failed.', 'errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!is_numeric($data['quantity']) || (int) $data['quantity'] <= 0) {
            return $this->json(['status' => 'error', 'message' => 'Quantity must be a positive integer.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!empty($data['customer_email']) && !filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['status' => 'error', 'message' => 'Invalid email format.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $product = $this->productRepository->find($data['product_id']);
            if (!$product) {
                return $this->json(['status' => 'error', 'message' => 'Product not found.'], JsonResponse::HTTP_NOT_FOUND);
            }

            $quantity = (int) $data['quantity'];

            if ($product->getQuantity() < $quantity) {
                return $this->json([
                    'status'    => 'error',
                    'message'   => 'Insufficient stock.',
                    'available' => $product->getQuantity(),
                    'requested' => $quantity,
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $user      = $this->getUser();
            $userEmail = method_exists($user, 'getEmail') ? $user->getEmail() : $user->getUserIdentifier();

            $customer = $this->customerRepository->findOneBy(['email' => $userEmail]);
            if (!$customer && !empty($data['customer_email'])) {
                $customer = $this->customerRepository->findOneBy(['email' => $data['customer_email']]);
            }

            if (!$customer) {
                $customer = new Customer();
                $customer->setEmail($userEmail);
                $customer->setCreatedBy($user);
                $this->entityManager->persist($customer);
            }

            $customer->setName($data['customer_name']);
            if (!empty($data['customer_phone']))   $customer->setPhone($data['customer_phone']);
            if (!empty($data['customer_address'])) $customer->setAddress($data['customer_address']);

            $order = new Order();
            $order->setProduct($product);
            $order->setQuantity($quantity);
            $order->setCustomer($customer);
            $order->setStatus('Pending');
            $order->setCreatedBy($user);

            $product->setQuantity($product->getQuantity() - $quantity);
            $this->deductStock($product, $quantity, $user);

            $this->entityManager->persist($order);

            // ✅ Sales recording
            $sale = new Sales();
            $sale->setProduct($product);
            $sale->setQuantity($quantity);
            $sale->setTotalAmount($product->getPrice() * $quantity);
            $sale->setSaleDate(new \DateTimeImmutable());
            $this->entityManager->persist($sale);

            $this->entityManager->flush();

            $formattedOrder = $this->formatOrder($order);

            try {
                $this->pusher->orderPlaced($formattedOrder);
                $this->pusher->stockUpdated($product->getId(), $product->getName(), $product->getQuantity());
            } catch (\Exception $pusherEx) {
                $this->logger->warning('Pusher notification failed', ['error' => $pusherEx->getMessage()]);
            }

            // FCM push notification → confirm order to the customer
            try {
                $this->notifications->sendToUser(
                    $user,
                    'Order Placed!',
                    "Your order for {$product->getName()} (×{$quantity}) has been received.",
                    ['type' => 'order_status', 'orderId' => (string) $order->getId(), 'status' => 'Pending']
                );
            } catch (\Exception $e) {
                $this->logger->warning('FCM notification failed', ['error' => $e->getMessage()]);
            }

            return $this->json([
                'status'  => 'success',
                'message' => 'Order placed successfully.',
                'order'   => $formattedOrder,
            ], JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {
            $this->logger->error('Order creation failed', ['error' => $e->getMessage()]);
            return $this->json(['status' => 'error', 'message' => 'Failed to place order. Please try again.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // =========================================================================
    // GET /api/orders
    // =========================================================================
    #[Route('/orders', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(): JsonResponse
    {
        try {
            $user      = $this->getUser();
            $userEmail = method_exists($user, 'getEmail') ? $user->getEmail() : $user->getUserIdentifier();
            $customer  = $this->customerRepository->findOneBy(['email' => $userEmail]);

            if (!$customer) {
                return $this->json(['status' => 'success', 'orders' => [], 'total' => 0]);
            }

            $orders = $this->orderRepository->findBy(['customer' => $customer], ['createdAt' => 'DESC']);

            return $this->json([
                'status'   => 'success',
                'customer' => $customer->getName(),
                'orders'   => array_map(fn(Order $o) => $this->formatOrder($o), $orders),
                'total'    => count($orders),
            ]);

        } catch (\Exception $e) {
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // =========================================================================
    // GET /api/orders/{id}
    // =========================================================================
    #[Route('/orders/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(int $id): JsonResponse
    {
        if ($id <= 0) {
            return $this->json(['status' => 'error', 'message' => 'Invalid order ID.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $order = $this->orderRepository->find($id);
            if (!$order) {
                return $this->json(['status' => 'error', 'message' => 'Order not found.'], JsonResponse::HTTP_NOT_FOUND);
            }

            $user      = $this->getUser();
            $userEmail = method_exists($user, 'getEmail') ? $user->getEmail() : $user->getUserIdentifier();

            // Staff can see any order; customers only their own
            $roles = $user->getRoles();
            if (!in_array('ROLE_STAFF', $roles) && !in_array('ROLE_ADMIN', $roles)) {
                if ($order->getCustomer()?->getEmail() !== $userEmail) {
                    return $this->json(['status' => 'error', 'message' => 'Access denied.'], JsonResponse::HTTP_FORBIDDEN);
                }
            }

            return $this->json(['status' => 'success', 'order' => $this->formatOrder($order)]);

        } catch (\Exception $e) {
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // =========================================================================
    // PATCH /api/orders/{id}/cancel
    // =========================================================================
    #[Route('/orders/{id}/cancel', name: 'cancel', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(int $id): JsonResponse
    {
        if ($id <= 0) {
            return $this->json(['status' => 'error', 'message' => 'Invalid order ID.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $order = $this->orderRepository->find($id);
            if (!$order) {
                return $this->json(['status' => 'error', 'message' => 'Order not found.'], JsonResponse::HTTP_NOT_FOUND);
            }

            $user      = $this->getUser();
            $userEmail = method_exists($user, 'getEmail') ? $user->getEmail() : $user->getUserIdentifier();

            if ($order->getCustomer()?->getEmail() !== $userEmail) {
                return $this->json(['status' => 'error', 'message' => 'Access denied.'], JsonResponse::HTTP_FORBIDDEN);
            }

            if ($order->getStatus() === 'Cancelled') {
                return $this->json(['status' => 'error', 'message' => 'Order is already cancelled.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            if ($order->getStatus() !== 'Pending') {
                return $this->json([
                    'status'         => 'error',
                    'message'        => 'Only pending orders can be cancelled.',
                    'current_status' => $order->getStatus(),
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $product  = $order->getProduct();
            $quantity = $order->getQuantity();

            if ($product) {
                $product->setQuantity($product->getQuantity() + $quantity);
                $this->restoreStock($product, $quantity);
            }

            $order->setStatus('Cancelled');

            // Reverse sales record
            if ($product) {
                $reverseSale = new Sales();
                $reverseSale->setProduct($product);
                $reverseSale->setQuantity(-$quantity);
                $reverseSale->setTotalAmount(-($product->getPrice() * $quantity));
                $reverseSale->setSaleDate(new \DateTimeImmutable());
                $this->entityManager->persist($reverseSale);
            }

            $this->entityManager->flush();

            $formattedOrder = $this->formatOrder($order);

            try {
                $this->pusher->orderStatusUpdated($formattedOrder);
                if ($product) {
                    $this->pusher->stockUpdated($product->getId(), $product->getName(), $product->getQuantity());
                }
            } catch (\Exception $pusherEx) {
                $this->logger->warning('Pusher notification failed', ['error' => $pusherEx->getMessage()]);
            }

            return $this->json([
                'status'  => 'success',
                'message' => 'Order cancelled successfully.',
                'order'   => $formattedOrder,
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Order cancellation failed', ['error' => $e->getMessage()]);
            return $this->json(['status' => 'error', 'message' => 'Failed to cancel order. Please try again.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // =========================================================================
    // Stock helpers
    // =========================================================================

    private function deductStock(object $product, int $quantity, object $user): void
    {
        $stockRecords = $this->stockRepository->findBy(
            ['product' => $product, 'isHistoryEntry' => false],
            ['lastUpdated' => 'DESC']
        );

        $remaining = $quantity;
        foreach ($stockRecords as $stock) {
            if ($remaining <= 0) break;
            $stockQty = $stock->getQuantity();
            if ($stockQty <= 0) continue;
            if ($stockQty >= $remaining) {
                $stock->setQuantity($stockQty - $remaining);
                $remaining = 0;
            } else {
                $remaining -= $stockQty;
                $stock->setQuantity(0);
            }
        }

        $historyEntry = new Stock();
        $historyEntry->setProduct($product);
        $historyEntry->setQuantity($quantity);
        $historyEntry->setIsHistoryEntry(true);
        $historyEntry->setType('order_deduction');
        $historyEntry->setCreatedAt(new \DateTimeImmutable());
        $historyEntry->setLastUpdated(new \DateTimeImmutable());
        $historyEntry->setCreatedBy(null);
        $this->entityManager->persist($historyEntry);
    }

    private function restoreStock(object $product, int $quantity): void
    {
        $stockRecords = $this->stockRepository->findBy(
            ['product' => $product, 'isHistoryEntry' => false],
            ['lastUpdated' => 'DESC']
        );

        if (!empty($stockRecords)) {
            $stockRecords[0]->setQuantity($stockRecords[0]->getQuantity() + $quantity);
        } else {
            $newStock = new Stock();
            $newStock->setProduct($product);
            $newStock->setQuantity($quantity);
            $newStock->setIsHistoryEntry(false);
            $this->entityManager->persist($newStock);
        }

        $historyEntry = new Stock();
        $historyEntry->setProduct($product);
        $historyEntry->setQuantity($quantity);
        $historyEntry->setIsHistoryEntry(true);
        $historyEntry->setType('order_cancelled');
        $historyEntry->setCreatedAt(new \DateTimeImmutable());
        $historyEntry->setLastUpdated(new \DateTimeImmutable());
        $historyEntry->setCreatedBy(null);
        $this->entityManager->persist($historyEntry);
    }

    // =========================================================================
    // Format helper
    // =========================================================================

    private function formatOrder(Order $order): array
    {
        $product  = $order->getProduct();
        $imageUrl = $product?->getImage()
    ? 'https://webdevfinalproject-production-a2ea.up.railway.app/uploads/images/' . $product->getImage()
    : null;
        return [
            'id'          => $order->getId(),
            'status'      => $order->getStatus(),
            'quantity'    => $order->getQuantity(),
            'total_price' => $order->getTotalPrice(),
            'created_at'  => $order->getCreatedAt()?->format('Y-m-d H:i:s'),
            'product'     => $product ? [
                'id'       => $product->getId(),
                'name'     => $product->getName(),
                'price'    => $product->getPrice(),
                'image'    => $imageUrl,
                'quantity' => $product->getQuantity(),
            ] : null,
            'customer'    => [
                'id'      => $order->getCustomer()?->getId(),
                'name'    => $order->getCustomer()?->getName(),
                'email'   => $order->getCustomer()?->getEmail(),
                'phone'   => $order->getCustomer()?->getPhone(),
                'address' => $order->getCustomer()?->getAddress(),
            ],
        ];
    }
}