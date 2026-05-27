<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;

#[Route('/api/user', name: 'api_user_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository         $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface        $logger,
    ) {}

    #[Route('/fcm-token', name: 'fcm_token', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function saveFcmToken(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['fcmToken'])) {
                return new JsonResponse(
                    ['error' => 'Missing fcmToken field'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $fcmToken = trim($data['fcmToken']);

            if (empty($fcmToken)) {
                return new JsonResponse(
                    ['error' => 'FCM token cannot be empty'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            /** @var \App\Entity\User $user */
            $user = $this->getUser();

            if (null === $user) {
                return new JsonResponse(
                    ['error' => 'User not authenticated'],
                    JsonResponse::HTTP_UNAUTHORIZED
                );
            }

            // Store the FCM token
            $user->setFcmToken($fcmToken);
            
            $this->em->flush();

            $this->logger->info('FCM token saved for user', [
                'userId' => $user->getId(),
                'email'  => $user->getEmail(),
                'token'  => substr($fcmToken, 0, 20) . '...', // Log only part of token for security
            ]);

            return new JsonResponse([
                'success' => true,
                'message' => 'FCM token saved successfully',
                'userId'  => $user->getId(),
            ], JsonResponse::HTTP_OK);

        } catch (\Throwable $e) {
            $this->logger->error('Error saving FCM token', [
                'error'     => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return new JsonResponse(
                ['error' => 'Failed to save FCM token: ' . $e->getMessage()],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
