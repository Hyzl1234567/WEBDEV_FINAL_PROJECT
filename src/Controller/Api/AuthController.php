<?php

namespace App\Controller\Api;

use App\Entity\Customer;
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
    private const GOOGLE_ALLOWED_ROLES = ['ROLE_CUSTOMER', 'ROLE_STAFF', 'ROLE_USER'];

    public function __construct(
        private readonly FirebaseAuthService      $firebaseAuth,
        private readonly UserRepository           $userRepository,
        private readonly EntityManagerInterface   $em,
        private readonly JWTTokenManagerInterface $jwtTokenManager,
        private readonly LoggerInterface          $logger,
    ) {}

    #[Route('/google', name: 'google', methods: ['POST'])]
    public function googleAuth(Request $request): JsonResponse
    {
        try {
            $this->logger->info('Firebase Google Sign-In request received');

            $data    = json_decode($request->getContent(), true);
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
            $user  = $this->userRepository->findOneBy(['email' => $email]);

            if (null === $user) {
                $baseUsername = explode('@', $email)[0];
                $username     = $baseUsername;
                if ($this->userRepository->findOneBy(['username' => $username])) {
                    $username = $baseUsername . '_' . substr(uniqid(), -4);
                }

                $user = new User();
                $user->setFirebaseUid($firebaseUser['uid']);
                $user->setEmail($email);
                $user->setUsername($username);
                $user->setFullName($firebaseUser['name'] ?? $baseUsername);
                $user->setDisplayName($firebaseUser['name'] ?? $baseUsername);
                $user->setProfilePictureUrl($firebaseUser['photo'] ?? null);
                $user->setRoles(['ROLE_CUSTOMER']);
                $user->setPassword('');
                $user->setStatus('active');
                $user->setIsVerified(true);
                $user->setVerificationToken(null);

                $this->em->persist($user);
                $this->logger->info('New Google customer created', ['email' => $email]);

            } else {
                $significantRoles = array_diff($user->getRoles(), ['ROLE_USER']);
                $hasBlockedRole   = !empty(array_diff($significantRoles, self::GOOGLE_ALLOWED_ROLES));

                if ($hasBlockedRole) {
                    $this->logger->warning('Blocked Google Sign-In attempt', [
                        'email' => $email,
                        'roles' => $user->getRoles(),
                    ]);

                    return new JsonResponse([
                        'error' => 'This account must sign in using username and password.',
                    ], JsonResponse::HTTP_FORBIDDEN);
                }

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

                $this->logger->info('Existing Google user logged in', [
                    'email' => $email,
                    'roles' => $user->getRoles(),
                ]);
            }

            $this->em->flush();

            $jwt      = $this->jwtTokenManager->create($user);

            // Find or create a Customer record so the app's Pusher channel customer-{id} is always populated
            $customer = $this->em->getRepository(Customer::class)->findOneBy(['email' => $email]);
            if (!$customer) {
                $customer = new Customer();
                $customer->setName($user->getFullName() ?? $user->getUsername());
                $customer->setEmail($email);
                $customer->setCreatedBy($user);
                $this->em->persist($customer);
                $this->em->flush();
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

        } catch (\Throwable $e) {
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
