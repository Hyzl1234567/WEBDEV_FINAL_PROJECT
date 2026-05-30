<?php

namespace App\EventSubscriber;

use App\Service\ActivityLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LoginLogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ActivityLogger $activityLogger,
        private RequestStack   $requestStack,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'onLogin',
            LogoutEvent::class           => 'onLogout',
        ];
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        // API logins (/api/login) come from the mobile app — never log those
        $request = $this->requestStack->getCurrentRequest();
        if ($request && str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

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