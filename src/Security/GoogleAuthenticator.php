<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class GoogleAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $client = $this->clientRegistry->getClient('google');
        $googleUser = $client->fetchUser();
        $email = $googleUser->getEmail();

        // Check if user already exists
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $user->setStatus('active');
            $user->setIsVerified(true);
            $user->setVerificationToken(null);
            $user->setPassword('');

            // Generate unique username from email (e.g. juan@gmail.com → juan)
            $baseUsername = explode('@', $email)[0];
            $username = $baseUsername;
            $counter = 1;
            while ($this->userRepository->findOneBy(['username' => $username])) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            $user->setUsername($username);

            // Build full_name from Google profile, fallback to username if unavailable
            $firstName = $googleUser->getFirstName() ?? '';
            $lastName  = $googleUser->getLastName() ?? '';
            $fullName  = trim($firstName . ' ' . $lastName);
            if (empty($fullName)) {
                $fullName = $baseUsername;
            }
            $user->setFullName($fullName);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        // If the user registered via email but hasn't verified yet, still allow Google login
        // and mark them as verified since Google confirmed their email ownership
        if (!$user->isVerified()) {
            $user->setIsVerified(true);
            $user->setVerificationToken(null);
            $this->entityManager->flush();
        }

        return new SelfValidatingPassport(
            new UserBadge($email, function () use ($user) {
                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse('/profile');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set('google_auth_error', $exception->getMessageKey());
        return new RedirectResponse('/login');
    }
}