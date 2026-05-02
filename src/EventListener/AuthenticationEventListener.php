<?php

namespace App\EventListener;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class AuthenticationEventListener
{
    public function __construct(private EntityManagerInterface $em) {}

    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user    = $event->getAuthenticatedToken()->getUser();
        $request = $event->getRequest();

        if (!$user instanceof User) return;

        $log = new ActivityLog();
        $log->setUser($user);
        $log->setUsername($user->getUsername());
        $log->setRole($this->getPrimaryRole($user->getRoles()));
        $log->setAction('login');
        $log->setEntity('User');
        $log->setEntityId($user->getId());
        $log->setDescription($user->getUsername() . ' logged in successfully.');
        $log->setIpAddress($request->getClientIp());
        $log->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($log);
        $this->em->flush();
    }

    #[AsEventListener(event: LogoutEvent::class)]
    public function onLogout(LogoutEvent $event): void
    {
        $token   = $event->getToken();
        $request = $event->getRequest();

        if (!$token) return;

        $user = $token->getUser();

        if (!$user instanceof User) return;

        $log = new ActivityLog();
        $log->setUser($user);
        $log->setUsername($user->getUsername());
        $log->setRole($this->getPrimaryRole($user->getRoles()));
        $log->setAction('logout');
        $log->setEntity('User');
        $log->setEntityId($user->getId());
        $log->setDescription($user->getUsername() . ' logged out.');
        $log->setIpAddress($request->getClientIp());
        $log->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($log);
        $this->em->flush();
    }

    private function getPrimaryRole(array $roles): string
    {
        if (in_array('ROLE_ADMIN', $roles)) return 'ROLE_ADMIN';
        if (in_array('ROLE_STAFF', $roles)) return 'ROLE_STAFF';
        return 'ROLE_USER';
    }
}