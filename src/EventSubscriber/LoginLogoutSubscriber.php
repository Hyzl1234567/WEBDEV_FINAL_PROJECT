<?php

namespace App\EventSubscriber;

use App\Service\ActivityLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LoginLogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(private ActivityLogger $activityLogger) {}

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'onLogin',
            LogoutEvent::class           => 'onLogout',
        ];
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof \App\Entity\User) {
            $this->activityLogger->logLogin($user);
        }
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();

        if ($token && $token->getUser() instanceof \App\Entity\User) {
            $this->activityLogger->logLogout($token->getUser());
        }
    }
}