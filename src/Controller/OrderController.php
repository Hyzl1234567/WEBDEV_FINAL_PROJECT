<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\Order1Type;
use App\Repository\OrderRepository;
use App\Repository\StockRepository;
use App\Service\ActivityLogger;
use App\Service\NotificationService;
use App\Service\PusherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
#[IsGranted('ROLE_USER')]
final class OrderController extends AbstractController
{
    public function __construct(
        private readonly ActivityLogger      $activityLogger,
        private readonly PusherService       $pusher,
        private readonly NotificationService $notifications,
    ) {}

    #[Route(name: 'app_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findAllWithRelations(),
        ]);
    }

    #[Route('/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, StockRepository $stockRepository): Response
    {
        $order = new Order();
        $form = $this->createForm(Order1Type::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setCreatedBy($this->getUser());
            $entityManager->persist($order);

            // Deduct ordered quantity from the product's main stock
            $product = $order->getProduct();
            if ($product) {
                $stock = $stockRepository->findMainStockByProduct($product->getId());
                if ($stock && $stock->getQuantity() >= $order->getQuantity()) {
                    $newQty = $stock->getQuantity() - $order->getQuantity();
                    $stock->setQuantity($newQty);
                    $product->setQuantity($newQty);
                }
            }

            $entityManager->flush();

            $this->activityLogger->logOrderPlaced(
                $this->getUser(),
                $order->getId(),
                $order->getCustomer()?->getName() ?? 'Unknown Customer',
                $order->getProduct()?->getName() ?? 'Unknown Product',
                $order->getQuantity(),
                $order->getTotalPrice()
            );

            $this->addFlash('success', 'Order created successfully!');
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if (!$this->canEditOrDelete($order)) {
            $this->addFlash('error', 'You do not have permission to edit this order. You can only edit your own records.');
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(Order1Type::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $snapshot = [
                'customer'    => $order->getCustomer()?->getName(),
                'product'     => $order->getProduct()?->getName(),
                'quantity'    => $order->getQuantity(),
                'total_price' => $order->getTotalPrice(),
            ];

            $entityManager->flush();

            $this->activityLogger->logUpdate(
                $this->getUser(),
                'Order',
                $order->getId(),
                sprintf('Order #%d - Customer: %s (ID: %d)',
                    $order->getId(),
                    $order->getCustomer()?->getName() ?? 'Deleted Customer',
                    $order->getId()
                ),
                $snapshot
            );

            // Notify the customer app via Pusher (real-time) and FCM (push notification)
            $formattedOrder = [
                'id'          => $order->getId(),
                'status'      => $order->getStatus(),
                'quantity'    => $order->getQuantity(),
                'total_price' => $order->getTotalPrice(),
                'created_at'  => $order->getCreatedAt()?->format('Y-m-d H:i:s'),
                'product'     => $order->getProduct() ? [
                    'id'       => $order->getProduct()->getId(),
                    'name'     => $order->getProduct()->getName(),
                    'price'    => $order->getProduct()->getPrice(),
                    'image'    => null,
                    'quantity' => $order->getProduct()->getQuantity(),
                ] : null,
                'customer'    => [
                    'id'      => $order->getCustomer()?->getId(),
                    'name'    => $order->getCustomer()?->getName(),
                    'email'   => $order->getCustomer()?->getEmail(),
                    'phone'   => $order->getCustomer()?->getPhone(),
                    'address' => $order->getCustomer()?->getAddress(),
                ],
            ];

            try {
                $this->pusher->orderStatusUpdated($formattedOrder);
            } catch (\Throwable $e) {
                // Non-fatal — log but don't break the web response
            }

            try {
                $orderUser = $order->getCreatedBy();
                if ($orderUser) {
                    $this->notifications->sendToUser(
                        $orderUser,
                        'Order Status Updated',
                        sprintf('Your order #%d is now: %s', $order->getId(), $order->getStatus()),
                        [
                            'type'    => 'order_status',
                            'orderId' => (string) $order->getId(),
                            'status'  => $order->getStatus(),
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // Non-fatal
            }

            $this->addFlash('success', 'Order updated successfully!');
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if (!$this->canEditOrDelete($order)) {
            $this->addFlash('error', 'You do not have permission to delete this order. You can only delete your own records.');
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->getPayload()->getString('_token'))) {
            $orderId      = $order->getId();
            $customerName = $order->getCustomer()?->getName() ?? 'Deleted Customer';
            $productName  = $order->getProduct()?->getName()  ?? 'Deleted Product';

            $snapshot = [
                'customer'    => $customerName,
                'product'     => $productName,
                'quantity'    => $order->getQuantity(),
                'total_price' => $order->getTotalPrice(),
                'deleted_at'  => (new \DateTimeImmutable())->format('c'),
            ];

            $this->activityLogger->logDelete(
                $this->getUser(),
                'Order',
                $orderId,
                sprintf('Order #%d - Customer: %s, Product: %s (ID: %d)',
                    $orderId,
                    $customerName,
                    $productName,
                    $orderId
                ),
                $snapshot
            );

            $entityManager->remove($order);
            $entityManager->flush();

            $this->addFlash('success', 'Order deleted successfully!');
        }

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }

    private function canEditOrDelete(Order $order): bool
    {
        $currentUser = $this->getUser();

        if (!$order->getCreatedBy()) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $currentUser->getRoles()) || in_array('ROLE_STAFF', $currentUser->getRoles())) {
            return true;
        }

        return false;
    }
}
