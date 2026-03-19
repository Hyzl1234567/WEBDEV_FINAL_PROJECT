<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


final class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        EmailVerificationService $emailService
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        // ✅ Validate input
        if (
            !$data ||
            !isset($data['username']) ||
            !isset($data['password']) ||
            !isset($data['email'])
        ) {
            return new JsonResponse([
                'message' => 'Invalid request data'
            ], 400);
        }

        // ✅ Check duplicate username
        $existingUser = $entityManager->getRepository(User::class)
            ->findOneBy(['username' => $data['username']]);

        if ($existingUser) {
            return new JsonResponse([
                'message' => 'Username already exists'
            ], 409);
        }

        // ✅ Check duplicate email
        $existingEmail = $entityManager->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);

        if ($existingEmail) {
            return new JsonResponse([
                'message' => 'Email already in use'
            ], 409);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setIsVerified(false);
        $user->setFullName('Default Name');
        $user->setStatus('active');
        $user->setCreatedAt(new \DateTimeImmutable());

        // ✅ Hash password
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($hashedPassword);

        // ✅ Generate verification token
        $token = $emailService->generateToken();
        $user->setVerificationToken($token);

        $entityManager->persist($user);
        $entityManager->flush();

        // ✅ Send email
        $emailService->sendEmail($user->getEmail(), $token);

        return new JsonResponse([
            'message' => 'Registration successful. Please check your email.'
        ], 201);
    }

    #[Route('/verify-email', name: 'verify_email', methods: ['GET'])]
    public function verifyEmail(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        $token = $request->query->get('token');

        if (!$token) {
            return new Response("Invalid token");
        }

        $user = $entityManager->getRepository(User::class)
            ->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            return new Response("Invalid or expired token");
        }

        // ✅ Verify user
        $user->setIsVerified(true);
        $user->setVerificationToken(null);

        $entityManager->flush();

        return new Response("Email verified successfully! You can now login.");
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['username']) || !isset($data['password'])) {
            return new JsonResponse([
                'message' => 'Invalid request data'
            ], 400);
        }

        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $data['username']]);

        if (!$user) {
            return new JsonResponse([
                'message' => 'User not found'
            ], 401);
        }

        // ✅ BLOCK LOGIN if not verified
        if (!$user->isVerified()) {
            return new JsonResponse([
                'message' => 'Please verify your email first'
            ], 403);
        }

        // ✅ Password check
        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse([
                'message' => 'Invalid password'
            ], 401);
        }

        return new JsonResponse([
            'message' => 'Login successful',
            'username' => $user->getUsername()
        ]);
    }
    
}