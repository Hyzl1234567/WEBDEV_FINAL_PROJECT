<?php

namespace App\Controller;

use App\Repository\ActivityLogRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        UserRepository $userRepository,
        ActivityLogRepository $activityLogRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Get statistics
        $totalUsers = $userRepository->count([]);

        // Count admins, staff, and customers by role
        $allUsers       = $userRepository->findAll();
        $totalAdmins    = 0;
        $totalStaff     = 0;
        $totalCustomers = 0;

        foreach ($allUsers as $user) {
            if (in_array('ROLE_ADMIN',    $user->getRoles(), true)) $totalAdmins++;
            if (in_array('ROLE_STAFF',    $user->getRoles(), true)) $totalStaff++;
            if (in_array('ROLE_CUSTOMER', $user->getRoles(), true)) $totalCustomers++;
        }

        // Count products
        $totalProducts = 0;
        try {
            $result = $entityManager->getConnection()->executeQuery('SELECT COUNT(*) as count FROM product');
            $totalProducts = $result->fetchOne();
        } catch (\Exception $e) {
            $totalProducts = 0;
        }

        // Count categories
        $totalCategories = 0;
        try {
            $result = $entityManager->getConnection()->executeQuery('SELECT COUNT(*) as count FROM category');
            $totalCategories = $result->fetchOne();
        } catch (\Exception $e) {
            $totalCategories = 0;
        }

        // Sum total stock quantity (main entries only, excluding history)
        $totalStocks = 0;
        try {
            $result = $entityManager->getConnection()->executeQuery(
                'SELECT COALESCE(SUM(quantity), 0) FROM stock WHERE is_history_entry = 0'
            );
            $totalStocks = (int) $result->fetchOne();
        } catch (\Exception $e) {
            $totalStocks = 0;
        }

        // Get recent activities
        $recentActivities = $activityLogRepository->findRecentActivities(10);

        $categories = [
            ['name' => 'Coffee',   'image' => 'coffee.png',   'description' => 'Rich espresso and brewed coffee.'],
            ['name' => 'Tea',      'image' => 'tea.png',      'description' => 'Soothing hot or iced teas.'],
            ['name' => 'Smoothie', 'image' => 'smoothie.png', 'description' => 'Fresh and fruity blends.'],
            ['name' => 'Pastry',   'image' => 'pastry.png',   'description' => 'Crispy and buttery delights.'],
            ['name' => 'Dessert',  'image' => 'dessert.png',  'description' => 'Sweet treats and indulgent bites.'],
            ['name' => 'Vegan',    'image' => 'vegan.png',    'description' => 'Plant-based goodness.'],
            ['name' => 'Seasonal', 'image' => 'seasonal.png', 'description' => 'Limited-time seasonal favorites.'],
            ['name' => 'Combo',    'image' => 'combo.png',    'description' => 'Perfect drink and snack combos.'],
        ];

        // Count total orders
        $totalOrders = 0;
        try {
            $result = $entityManager->getConnection()->executeQuery('SELECT COUNT(*) FROM `order`');
            $totalOrders = (int) $result->fetchOne();
        } catch (\Exception $e) {
            $totalOrders = 0;
        }

        return $this->render('dashboard/index.html.twig', [
            'categories'       => $categories,
            'totalUsers'       => $totalUsers,
            'totalAdmins'      => $totalAdmins,
            'totalStaff'       => $totalStaff,
            'totalCustomers'   => $totalCustomers,   // ← new
            'totalProducts'    => $totalProducts,
            'totalCategories'  => $totalCategories,
            'totalStocks'      => $totalStocks,
            'totalOrders'      => $totalOrders,
            'recentActivities' => $recentActivities,
        ]);
    }

    #[Route('/dashboard/recent-activities', name: 'app_dashboard_recent_activities', methods: ['GET'])]
    public function recentActivitiesJson(ActivityLogRepository $activityLogRepository): JsonResponse
    {
        $activities = $activityLogRepository->findRecentActivities(10);

        $data = array_map(function ($log) {
            return [
                'id'          => $log->getId(),
                'username'    => $log->getUsername() ?? $log->getUser()?->getUsername(),
                'userId'      => $log->getUser()?->getId(),
                'role'        => $log->getRole(),
                'action'      => $log->getAction(),
                'entity'      => $log->getEntity(),
                'entityId'    => $log->getEntityId(),
                'description' => $log->getDescription(),
                'createdAt'   => $log->getCreatedAt()?->format('M d, H:i'),
            ];
        }, $activities);

        return $this->json($data);
    }
}