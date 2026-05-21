<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    // ─── Allowed roles ────────────────────────────────────────────────────────
    private const ROLE_MAP = [
        'ROLE_ADMIN'    => ['ROLE_ADMIN'],
        'ROLE_STAFF'    => ['ROLE_STAFF'],
        'ROLE_CUSTOMER' => ['ROLE_CUSTOMER'],
        'ROLE_USER'     => ['ROLE_USER'],
    ];

    private function resolveRoles(string $role): array
    {
        return self::ROLE_MAP[$role] ?? ['ROLE_USER'];
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    #[Route('/', name: 'app_user_management_index')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user_management/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    // ─── New ─────────────────────────────────────────────────────────────────

    #[Route('/new', name: 'app_user_management_new')]
    public function new(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ActivityLogger $activityLogger
    ): Response {
        if ($request->isMethod('POST')) {
            $role = $request->request->get('role', 'ROLE_USER');

            $user = new User();
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUsername($request->request->get('username'));
            $user->setEmail($request->request->get('email'));
            $user->setFullName($request->request->get('full_name'));
            $user->setRoles($this->resolveRoles($role));   // ← uses map
            $user->setPassword(
                $passwordHasher->hashPassword($user, $request->request->get('password'))
            );
            $user->setStatus('active');

            $entityManager->persist($user);
            $entityManager->flush();

            $activityLogger->logCreate(
                $this->getUser(),
                'User',
                $user->getId(),
                sprintf('User: %s (ID: %d)', $user->getUsername(), $user->getId()),
                [
                    'username'  => $user->getUsername(),
                    'email'     => $user->getEmail(),
                    'full_name' => $user->getFullName(),
                    'role'      => $role,
                    'status'    => 'active',
                ]
            );

            $this->addFlash('success', 'User created successfully!');
            return $this->redirectToRoute('app_user_management_index');
        }

        return $this->render('user_management/new.html.twig');
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    #[Route('/{id}/edit', name: 'app_user_management_edit')]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        ActivityLogger $activityLogger
    ): Response {
        if ($request->isMethod('POST')) {
            // Snapshot BEFORE changes
            $snapshot = [
                'username'  => $user->getUsername(),
                'email'     => $user->getEmail(),
                'full_name' => $user->getFullName(),
                'role'      => $user->getRoles()[0] ?? 'ROLE_USER',
                'status'    => $user->getStatus(),
            ];

            $role = $request->request->get('role', 'ROLE_USER');

            $user->setUsername($request->request->get('username'));
            $user->setEmail($request->request->get('email'));
            $user->setFullName($request->request->get('full_name'));
            $user->setRoles($this->resolveRoles($role));   // ← uses map
            $user->setStatus($request->request->get('status'));

            $entityManager->flush();

            $activityLogger->logUpdate(
                $this->getUser(),
                'User',
                $user->getId(),
                sprintf('User: %s (ID: %d)', $user->getUsername(), $user->getId()),
                $snapshot
            );

            $this->addFlash('success', 'User updated successfully!');
            return $this->redirectToRoute('app_user_management_index');
        }

        return $this->render('user_management/edit.html.twig', [
            'user' => $user,
        ]);
    }

    // ─── Reset Password ───────────────────────────────────────────────────────

    #[Route('/{id}/reset-password', name: 'app_user_management_reset_password')]
    public function resetPassword(
        User $user,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ActivityLogger $activityLogger
    ): Response {
        if ($request->isMethod('POST')) {
            $newPassword     = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Passwords do not match!');
                return $this->redirectToRoute('app_user_management_reset_password', ['id' => $user->getId()]);
            }

            if (strlen($newPassword) < 6) {
                $this->addFlash('error', 'Password must be at least 6 characters!');
                return $this->redirectToRoute('app_user_management_reset_password', ['id' => $user->getId()]);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $entityManager->flush();

            $activityLogger->logUpdate(
                $this->getUser(),
                'User',
                $user->getId(),
                sprintf('User: %s (ID: %d)', $user->getUsername(), $user->getId()),
                [
                    'username' => $user->getUsername(),
                    'email'    => $user->getEmail(),
                    'role'     => $user->getRoles()[0] ?? 'ROLE_USER',
                    'note'     => 'password reset by admin',
                ]
            );

            $this->addFlash('success', 'Password reset successfully!');
            return $this->redirectToRoute('app_user_management_index');
        }

        return $this->render('user_management/reset_password.html.twig', [
            'user' => $user,
        ]);
    }

    // ─── Toggle Status ────────────────────────────────────────────────────────

    #[Route('/{id}/toggle-status', name: 'app_user_management_toggle_status')]
    public function toggleStatus(
        User $user,
        EntityManagerInterface $entityManager,
        ActivityLogger $activityLogger
    ): Response {
        $oldStatus  = $user->getStatus();
        $newStatus  = $oldStatus === 'active' ? 'disabled' : 'active';
        $message    = $newStatus === 'active' ? 'User account activated.' : 'User account disabled.';

        $user->setStatus($newStatus);
        $entityManager->flush();

        $activityLogger->logUpdate(
            $this->getUser(),
            'User',
            $user->getId(),
            sprintf('User: %s (ID: %d)', $user->getUsername(), $user->getId()),
            [
                'username'   => $user->getUsername(),
                'role'       => $user->getRoles()[0] ?? 'ROLE_USER',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        );

        $this->addFlash('success', $message);
        return $this->redirectToRoute('app_user_management_index');
    }

    // ─── Archive ──────────────────────────────────────────────────────────────

    #[Route('/{id}/archive', name: 'app_user_management_archive')]
    public function archive(
        User $user,
        EntityManagerInterface $entityManager,
        ActivityLogger $activityLogger
    ): Response {
        $activityLogger->logUpdate(
            $this->getUser(),
            'User',
            $user->getId(),
            sprintf('User: %s (ID: %d)', $user->getUsername(), $user->getId()),
            [
                'username'   => $user->getUsername(),
                'role'       => $user->getRoles()[0] ?? 'ROLE_USER',
                'old_status' => $user->getStatus(),
                'new_status' => 'archived',
            ]
        );

        $user->setStatus('archived');
        $entityManager->flush();

        $this->addFlash('success', 'User account archived.');
        return $this->redirectToRoute('app_user_management_index');
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    #[Route('/{id}/delete', name: 'app_user_management_delete', methods: ['POST'])]
    public function delete(
        User $user,
        EntityManagerInterface $entityManager,
        ActivityLogger $activityLogger
    ): Response {
        $activityLogger->logDelete(
            $this->getUser(),
            'User',
            $user->getId(),
            sprintf('User: %s (ID: %d)', $user->getUsername(), $user->getId()),
            [
                'username'   => $user->getUsername(),
                'email'      => $user->getEmail(),
                'full_name'  => $user->getFullName(),
                'role'       => $user->getRoles()[0] ?? 'ROLE_USER',
                'status'     => $user->getStatus(),
                'deleted_at' => (new \DateTimeImmutable())->format('c'),
            ]
        );

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'User deleted successfully!');
        return $this->redirectToRoute('app_user_management_index');
    }
}