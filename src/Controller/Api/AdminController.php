<?php

namespace App\Controller\Api;

use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin', name: 'api_admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly UserRepository  $userRepository,
    ) {}

    /**
     * GET /api/admin/stats
     * Returns dashboard statistics: order counts per status, total revenue, user counts.
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $orders = $this->orderRepository->findAll();

        $statusCounts = [];
        $revenue = 0.0;

        foreach ($orders as $order) {
            $status = $order->getStatus() ?? 'Unknown';
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

            if ($status !== 'Cancelled') {
                $revenue += (float) ($order->getTotalPrice() ?? 0);
            }
        }

        $users = $this->userRepository->findAll();

        $customerCount = 0;
        $staffCount    = 0;
        $adminCount    = 0;

        foreach ($users as $user) {
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles))         $adminCount++;
            elseif (in_array('ROLE_STAFF', $roles))     $staffCount++;
            else                                         $customerCount++;
        }

        return $this->json([
            'orders' => [
                'total'      => count($orders),
                'pending'    => $statusCounts['Pending']    ?? 0,
                'confirmed'  => $statusCounts['Confirmed']  ?? 0,
                'preparing'  => $statusCounts['Preparing']  ?? 0,
                'ready'      => $statusCounts['Ready']      ?? 0,
                'processing' => $statusCounts['Processing'] ?? 0,
                'completed'  => $statusCounts['Completed']  ?? 0,
                'delivered'  => $statusCounts['Delivered']  ?? 0,
                'cancelled'  => $statusCounts['Cancelled']  ?? 0,
            ],
            'revenue' => [
                'total' => round($revenue, 2),
            ],
            'users' => [
                'total'     => count($users),
                'customers' => $customerCount,
                'staff'     => $staffCount,
                'admins'    => $adminCount,
            ],
        ]);
    }

    /**
     * GET /api/admin/users
     * Returns all registered users with their roles and FCM token status.
     */
    #[Route('/users', name: 'users', methods: ['GET'])]
    public function users(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        $data = array_map(function ($user) {
            $roles = $user->getRoles();

            if (in_array('ROLE_ADMIN', $roles))      $primaryRole = 'ROLE_ADMIN';
            elseif (in_array('ROLE_STAFF', $roles))  $primaryRole = 'ROLE_STAFF';
            elseif (in_array('ROLE_CUSTOMER', $roles)) $primaryRole = 'ROLE_CUSTOMER';
            else                                       $primaryRole = 'ROLE_USER';

            return [
                'id'          => $user->getId(),
                'username'    => $user->getUsername(),
                'email'       => $user->getEmail(),
                'roles'       => $roles,
                'primaryRole' => $primaryRole,
                'hasFcmToken' => !empty($user->getFcmToken()),
            ];
        }, $users);

        // Sort: admins first, then staff, then customers
        usort($data, function ($a, $b) {
            $order = ['ROLE_ADMIN' => 0, 'ROLE_STAFF' => 1, 'ROLE_CUSTOMER' => 2, 'ROLE_USER' => 3];
            return ($order[$a['primaryRole']] ?? 4) <=> ($order[$b['primaryRole']] ?? 4);
        });

        return $this->json([
            'users' => $data,
            'total' => count($data),
        ]);
    }
}
