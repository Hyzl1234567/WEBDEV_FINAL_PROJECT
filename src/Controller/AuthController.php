<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        EmailVerificationService $emailService,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (
            !$data ||
            !isset($data['username']) ||
            !isset($data['password']) ||
            !isset($data['email']) ||
            !isset($data['full_name'])
        ) {
            return new JsonResponse(['message' => 'Invalid request data'], 400);
        }

        $existingUser = $entityManager->getRepository(User::class)
            ->findOneBy(['username' => $data['username']]);
        if ($existingUser) {
            return new JsonResponse(['message' => 'Username already exists'], 409);
        }

        $existingEmail = $entityManager->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);
        if ($existingEmail) {
            return new JsonResponse(['message' => 'Email already in use'], 409);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);
        $user->setIsVerified(false);
        $user->setStatus('active');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        $token = $emailService->generateVerificationToken();
        $user->setVerificationToken($token);

        $entityManager->persist($user);
        $entityManager->flush();

        // ✅ Fixed: use correct route name
        $verificationUrl = $urlGenerator->generate(
            'app_verify_email',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // ✅ Fixed: try/catch so SMTP errors don't crash the response
        try {
            $emailService->sendVerificationEmail($user, $verificationUrl);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Account created but verification email could not be sent. Please contact support.'
            ], 201);
        }

        return new JsonResponse([
            'message' => 'Registration successful. Please check your email to verify your account.'
        ], 201);
    }

    // ✅ Kept for backwards compat but app_verify_email in RegistrationController is the real one
    #[Route('/verify-email-plain', name: 'verify_email', methods: ['GET'])]
    public function verifyEmail(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $token = $request->query->get('token');

        if (!$token) {
            return new Response('Invalid token');
        }

        $user = $entityManager->getRepository(User::class)
            ->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            return new Response('Invalid or expired token');
        }

        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $entityManager->flush();

        return new Response('✔ Email verified! You can now log in.');
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtTokenManager,
        CustomerRepository $customerRepository
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['username']) || !isset($data['password'])) {
            return new JsonResponse(['message' => 'Invalid request data'], 400);
        }

        $user = $entityManager->getRepository(User::class)
            ->findOneBy(['username' => $data['username']]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 401);
        }

        if (!$user->isVerified()) {
            return new JsonResponse(['message' => 'Please verify your email first'], 403);
        }

        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['message' => 'Invalid password'], 401);
        }

        // Generate JWT token
        $jwt = $jwtTokenManager->create($user);

        // Find or create a Customer record so the app's Pusher channel customer-{id} is always populated
        $customer = $customerRepository->findOneBy(['email' => $user->getEmail()]);
        if (!$customer) {
            $customer = new Customer();
            $customer->setName($user->getFullName() ?? $user->getUsername());
            $customer->setEmail($user->getEmail());
            $customer->setCreatedBy($user);
            $entityManager->persist($customer);
            $entityManager->flush();
        }

        return new JsonResponse([
            'token' => $jwt,
            'user'  => [
                'id'          => $user->getId(),
                'email'       => $user->getEmail(),
                'username'    => $user->getUsername(),
                'fullName'    => $user->getFullName(),
                'displayName' => $user->getDisplayName(),
                'roles'       => $user->getRoles(),
                'photo'       => $user->getProfilePictureUrl(),
                'customer_id' => $customer?->getId(),
            ],
        ], JsonResponse::HTTP_OK);
    }
}