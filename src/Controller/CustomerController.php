<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Form\CustomerType;
use App\Repository\CustomerRepository;
use App\Service\ActivityLogger;
use App\Service\NotificationService;
use App\Service\PusherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/customer')]
#[IsGranted('ROLE_USER')]
final class CustomerController extends AbstractController
{
    private ActivityLogger $activityLogger;
    private PusherService $pusher;
    private NotificationService $notifications;

    public function __construct(ActivityLogger $activityLogger, PusherService $pusher, NotificationService $notifications)
    {
        $this->activityLogger = $activityLogger;
        $this->pusher = $pusher;
        $this->notifications = $notifications;
    }

    #[Route(name: 'app_customer_index', methods: ['GET'])]
    public function index(CustomerRepository $customerRepository): Response
    {
        return $this->render('Customer/index.html.twig', [
            'customers' => $customerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_customer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setCreatedBy($this->getUser());

            $entityManager->persist($customer);
            $entityManager->flush();

            $snapshot = [
                'name'       => $customer->getName(),
                'email'      => $customer->getEmail() ?? 'N/A',
                'phone'      => $customer->getPhone() ?? 'N/A',
                'created_by' => $customer->getCreatedBy()?->getUsername(),
            ];

            $this->activityLogger->logCreate(
                $this->getUser(),
                'Customer',
                $customer->getId(),
                sprintf('Customer: %s (ID: %d)', $customer->getName(), $customer->getId()),
                $snapshot
            );

            $this->addFlash('success', 'Customer created successfully!');
            return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customer_show', methods: ['GET'])]
    public function show(Customer $customer): Response
    {
        return $this->render('Customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_customer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        if (!$this->canEditOrDelete($customer)) {
            $this->addFlash('error', 'You do not have permission to edit this customer. You need staff or admin privileges.');
            return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $snapshot = [
                'name'  => $customer->getName(),
                'email' => $customer->getEmail() ?? 'N/A',
                'phone' => $customer->getPhone() ?? 'N/A',
            ];

            $entityManager->flush();

            $this->activityLogger->logUpdate(
                $this->getUser(),
                'Customer',
                $customer->getId(),
                sprintf('Customer: %s (ID: %d)', $customer->getName(), $customer->getId()),
                $snapshot
            );

            $this->addFlash('success', 'Customer updated successfully!');
            return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customer_delete', methods: ['POST'])]
    public function delete(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        if (!$this->canEditOrDelete($customer)) {
            $this->addFlash('error', 'You do not have permission to delete this customer. You need staff or admin privileges.');
            return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->getPayload()->getString('_token'))) {
            if ($customer->getOrders()->count() > 0) {
                $this->addFlash('error', sprintf(
                    'Cannot delete customer "%s" because they have %d order(s). Please delete or reassign their orders first.',
                    $customer->getName(),
                    $customer->getOrders()->count()
                ));
                return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
            }

            $customerId   = $customer->getId();
            $customerName = $customer->getName();

            $snapshot = [
                'name'       => $customer->getName(),
                'email'      => $customer->getEmail() ?? 'N/A',
                'phone'      => $customer->getPhone() ?? 'N/A',
                'created_by' => $customer->getCreatedBy()?->getUsername(),
                'deleted_at' => (new \DateTimeImmutable())->format('c'),
            ];

            $this->activityLogger->logDelete(
                $this->getUser(),
                'Customer',
                $customerId,
                sprintf('Customer: %s (ID: %d)', $customerName, $customerId),
                $snapshot
            );

            $entityManager->remove($customer);
            $entityManager->flush();

            $this->addFlash('success', 'Customer deleted successfully!');
        }

        return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/orders', name: 'app_customer_orders', methods: ['GET', 'POST'])]
    public function orders(
        Customer $customer,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            $orderId   = $request->request->get('order_id');
            $newStatus = $request->request->get('status');
            $token     = $request->request->get('_token');

            $allowed = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Processing', 'Completed', 'Delivered', 'Cancelled'];

            if (
                $this->isCsrfTokenValid('order_status_' . $orderId, $token)
                && in_array($newStatus, $allowed)
            ) {
                $order = $entityManager->getRepository(Order::class)->find($orderId);

                if ($order && $order->getCustomer()?->getId() === $customer->getId()) {
                    $old = $order->getStatus();

                    $order->setStatus($newStatus);
                    $entityManager->flush();

                    $this->pusher->orderStatusUpdated([
                        'id'          => $order->getId(),
                        'status'      => $order->getStatus(),
                        'quantity'    => $order->getQuantity(),
                        'total_price' => $order->getTotalPrice(),
                        'created_at'  => $order->getCreatedAt()?->format('Y-m-d H:i:s'),
                        'product'     => $order->getProduct() ? [
                            'id'   => $order->getProduct()->getId(),
                            'name' => $order->getProduct()->getName(),
                        ] : null,
                        'customer'    => [
                            'id'    => $order->getCustomer()?->getId(),
                            'name'  => $order->getCustomer()?->getName(),
                            'email' => $order->getCustomer()?->getEmail(),
                        ],
                    ]);

                    // 🆕 Send FCM notification to customer
                    try {
                        $orderUser = $order->getCreatedBy();
                        if ($orderUser) {
                            $this->notifications->sendToUser(
                                $orderUser,
                                'Order Status Updated',
                                "Your order #{$order->getId()} is now: {$newStatus}",
                                ['type' => 'order_status', 'orderId' => (string) $order->getId(), 'status' => $newStatus]
                            );
                        }
                    } catch (\Exception $e) {
                        // Log but don't fail the request
                    }

                    $this->activityLogger->logUpdate(
                        $this->getUser(),
                        'Order',
                        $order->getId(),
                        sprintf('Order #%d status changed: %s → %s', $order->getId(), $old, $newStatus),
                        ['old_status' => $old, 'new_status' => $newStatus]
                    );

                    $this->addFlash('success', sprintf('Order #%d updated to %s.', $order->getId(), $newStatus));
                }
            }

            return $this->redirectToRoute('app_customer_orders', ['id' => $customer->getId()]);
        }

        return $this->render('Customer/orders.html.twig', [
            'customer' => $customer,
            'orders'   => $customer->getOrders(),
        ]);
    }

    private function canEditOrDelete(Customer $customer): bool
    {
        $currentUser = $this->getUser();

        if (
            in_array('ROLE_ADMIN', $currentUser->getRoles()) ||
            in_array('ROLE_STAFF', $currentUser->getRoles())
        ) {
            return true;
        }

        return false;
    }
}