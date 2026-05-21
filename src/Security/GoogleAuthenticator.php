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
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
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

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $user->setStatus('active');
            $user->setIsVerified(true);
            $user->setVerificationToken(null);
            $user->setPassword('');

            $baseUsername = explode('@', $email)[0];
            $username = $baseUsername;
            $counter = 1;
            while ($this->userRepository->findOneBy(['username' => $username])) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            $user->setUsername($username);

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

        // ✅ AFTER — blocks only admin
if (in_array('ROLE_ADMIN', $user->getRoles())) {
    throw new CustomUserMessageAuthenticationException(
        'Admin accounts cannot log in with Google. Please use your username and password.'
    );
}

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
    // ✅ Use getMessage() instead of getMessageKey() to get the clean human-readable message
    $request->getSession()->set('google_auth_error', $exception->getMessage());
    return new RedirectResponse('/login');
}
}