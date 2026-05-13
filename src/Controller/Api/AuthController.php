<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\FirebaseAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly FirebaseAuthService $firebaseAuth,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly JWTTokenManagerInterface $jwtTokenManager,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/google', name: 'google', methods: ['POST'])]
    public function googleAuth(Request $request): JsonResponse
    {
        try {
            $this->logger->info('Firebase Google Sign-In request received');

            $data = json_decode($request->getContent(), true);

            // Accept both 'idToken' (from React Native) and 'firebase_token' (legacy)
            $idToken = $data['idToken'] ?? $data['firebase_token'] ?? null;

            if (!$data || !$idToken) {
                return new JsonResponse(
                    ['error' => 'Missing idToken field'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $firebaseUser = $this->firebaseAuth->verifyToken($idToken);

            if (null === $firebaseUser) {
                return new JsonResponse(
                    ['error' => 'Invalid Firebase token'],
                    JsonResponse::HTTP_UNAUTHORIZED
                );
            }

            $email = $firebaseUser['email'];
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if (null === $user) {
                // Generate unique username from email prefix
                $baseUsername = explode('@', $email)[0];
                $username = $baseUsername;
                if ($this->userRepository->findOneBy(['username' => $username])) {
                    $username = $baseUsername . '_' . substr(uniqid(), -4);
                }

                $user = new User();
                $user->setFirebaseUid($firebaseUser['uid']);
                $user->setEmail($email);
                $user->setUsername($username);
                $user->setFullName($firebaseUser['name'] ?? $baseUsername);    // ✅ correct method
                $user->setDisplayName($firebaseUser['name'] ?? $baseUsername);
                $user->setProfilePictureUrl($firebaseUser['photo'] ?? null);
                $user->setRoles(['ROLE_USER']);
                $user->setPassword('');         // ✅ empty string — entity requires string not null
                $user->setStatus('active');     // ✅ required field — was missing before
                $user->setIsVerified(true);
                $user->setVerificationToken(null);

                $this->em->persist($user);
                $this->logger->info('New Google user created', ['email' => $email]);

            } else {
                // Update fields if changed
                if ($user->getDisplayName() !== ($firebaseUser['name'] ?? '')) {
                    $user->setDisplayName($firebaseUser['name'] ?? '');
                }
                if ($user->getFullName() !== ($firebaseUser['name'] ?? '')) {
                    $user->setFullName($firebaseUser['name'] ?? '');
                }
                if ($user->getProfilePictureUrl() !== ($firebaseUser['photo'] ?? null)) {
                    $user->setProfilePictureUrl($firebaseUser['photo'] ?? null);
                }
                if ($user->getFirebaseUid() !== $firebaseUser['uid']) {
                    $user->setFirebaseUid($firebaseUser['uid']);
                }

                $this->logger->info('Existing Google user logged in', ['email' => $email]);
            }

            $this->em->flush();

            $jwt = $this->jwtTokenManager->create($user);

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
                ],
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            $this->logger->error('Unexpected error during Firebase authentication', [
                'error'     => $e->getMessage(),
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);

            return new JsonResponse(
                ['error' => 'Authentication failed: ' . $e->getMessage()],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}