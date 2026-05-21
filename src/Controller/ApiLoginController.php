<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        // This method body is intentionally empty.
        // JWT is handled automatically by lexik_jwt_authentication
        // via the json_login success_handler in security.yaml.
        // This route just needs to exist so Symfony registers it.
        throw new \LogicException('This should never be reached.');
    }
}