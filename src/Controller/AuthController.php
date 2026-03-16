<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
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

        $user = new User();

        // FIX
        $user->setUsername($data['username']);

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );

        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'User registered successfully'
        ], 201);
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

        // FIX
        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $data['username']]);

        if (!$user) {
            return new JsonResponse([
                'message' => 'User not found'
            ], 401);
        }

        // Better password verification
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