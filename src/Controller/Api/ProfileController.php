<?php

namespace App\Controller\Api;

use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_profile_')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly OrderRepository $orderRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * GET /api/profile
     * Get the authenticated user's profile.
     * Requires authentication.
     */
    #[Route('/profile', name: 'show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(): JsonResponse
    {
        $user  = $this->getUser();
        $email = method_exists($user, 'getEmail') ? $user->getEmail() : null;

        $customer    = $email ? $this->customerRepository->findOneBy(['email' => $email]) : null;
        $totalOrders = 0;
        $totalSpent  = 0.0;

        if ($customer) {
            $orders      = $this->orderRepository->findBy(['customer' => $customer]);
            $totalOrders = count($orders);
            $totalSpent  = array_sum(array_map(fn($o) => $o->getTotalPrice() ?? 0, $orders));
        }

        return $this->json([
            'status' => 'success',
            'user'   => [
                'id'          => $user->getId(),
                'username'    => $user->getUserIdentifier(),
                'email'       => $email,
                'full_name'   => method_exists($user, 'getFullName') ? $user->getFullName() : null,
                'roles'       => $user->getRoles(),
                'is_verified' => method_exists($user, 'isVerified') ? $user->isVerified() : null,
                'created_at'  => method_exists($user, 'getCreatedAt') ? $user->getCreatedAt()?->format('Y-m-d H:i:s') : null,
                'stats'       => [
                    'total_orders' => $totalOrders,
                    'total_spent'  => round($totalSpent, 2),
                ],
            ],
        ]);
    }

    /**
     * PUT /api/profile
     * Update the authenticated user's profile.
     * Requires authentication.
     */
    #[Route('/profile', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // ✅ Validate JSON body
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json([
                'status'  => 'error',
                'message' => 'Invalid JSON body.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // ✅ Require at least one field
        if (empty($data['full_name']) && empty($data['phone']) && empty($data['address'])) {
            return $this->json([
                'status'  => 'error',
                'message' => 'At least one field is required: full_name, phone, or address.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        // ✅ Validate phone format if provided
        if (!empty($data['phone']) && !preg_match('/^[0-9+\-\s]{7,15}$/', $data['phone'])) {
            return $this->json([
                'status'  => 'error',
                'message' => 'Invalid phone number format.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user  = $this->getUser();
        $email = method_exists($user, 'getEmail') ? $user->getEmail() : null;

        if (!empty($data['full_name']) && method_exists($user, 'setFullName')) {
            $user->setFullName($data['full_name']);
        }

        $customer = $email ? $this->customerRepository->findOneBy(['email' => $email]) : null;

        if ($customer) {
            if (!empty($data['full_name']))  $customer->setName($data['full_name']);
            if (!empty($data['phone']))      $customer->setPhone($data['phone']);
            if (!empty($data['address']))    $customer->setAddress($data['address']);
        }

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json([
                'status'  => 'error',
                'message' => 'Failed to update profile. Please try again.',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'status'    => 'success',
            'message'   => 'Profile updated successfully.',
            'full_name' => method_exists($user, 'getFullName') ? $user->getFullName() : null,
            'email'     => $email,
        ]);
    }
}