<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ApiController extends AbstractController
{
    // GET /api — health check
    #[Route('/api', name: 'app_api', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'status'  => 'success',
            'message' => 'EcoBrew API is working',
            'data'    => []
        ]);
    }

    // GET /api/profile
    #[Route('/api/profile', name: 'api_profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'status'  => 'error',
                'message' => 'Not authenticated',
            ], 401);
        }

        return $this->json([
            'status'  => 'success',
            'message' => 'User profile fetched',
            'data'    => [
                'id'          => $user->getId(),
                'username'    => $user->getUsername(),
                'email'       => $user->getEmail(),
                'full_name'   => $user->getFullName(),
                'roles'       => $user->getRoles(),
                'status'      => $user->getStatus(),
                'is_verified' => $user->isVerified(),
            ]
        ]);
    }

    // POST /api/register
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        EmailVerificationService $emailVerificationService
    ): JsonResponse {
        $data     = json_decode($request->getContent(), true);
        $username = trim($data['username']  ?? '');
        $email    = trim($data['email']     ?? '');
        $password = $data['password']       ?? '';
        $fullName = trim($data['full_name'] ?? '');

        if (!$username || !$email || !$password || !$fullName) {
            return $this->json([
                'status'  => 'error',
                'message' => 'All fields are required: username, email, password, full_name',
            ], 400);
        }

        if (strlen($password) < 6) {
            return $this->json([
                'status'  => 'error',
                'message' => 'Password must be at least 6 characters.',
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'status'  => 'error',
                'message' => 'Invalid email address.',
            ], 400);
        }

        if ($userRepository->findOneBy(['username' => $username])) {
            return $this->json([
                'status'  => 'error',
                'message' => 'Username is already taken.',
            ], 409);
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            return $this->json([
                'status'  => 'error',
                'message' => 'Email is already in use.',
            ], 409);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setFullName($fullName);
        $user->setRoles(['ROLE_USER']);
        $user->setStatus('active');
        $user->setIsVerified(false);
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $verificationToken = $emailVerificationService->generateVerificationToken();
        $user->setVerificationToken($verificationToken);

        $entityManager->persist($user);
        $entityManager->flush();

        $verificationUrl = $this->generateUrl(
            'app_verify_email',
            ['token' => $verificationToken],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $emailVerificationService->sendVerificationEmail($user, $verificationUrl);

        return $this->json([
            'status'  => 'success',
            'message' => 'Account created! Please check your email to verify your account before logging in.',
            'data'    => [
                'id'          => $user->getId(),
                'username'    => $user->getUsername(),
                'email'       => $user->getEmail(),
                'full_name'   => $user->getFullName(),
                'is_verified' => false,
            ]
        ], 201);
    }

    // POST /api/login — intercepted by api firewall, this body never runs
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        return $this->json([
            'status'  => 'error',
            'message' => 'Authentication required',
        ], 401);
    }
}