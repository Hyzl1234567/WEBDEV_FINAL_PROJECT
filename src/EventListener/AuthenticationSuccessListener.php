<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\CustomerRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
    ) {}

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $customer = $this->customerRepository->findOneBy(['email' => $user->getEmail()]);

        $data = $event->getData();
        $data['user'] = [
            'id'          => $user->getId(),
            'email'       => $user->getEmail(),
            'username'    => $user->getUsername(),
            'fullName'    => $user->getFullName(),
            'displayName' => $user->getDisplayName(),
            'roles'       => $user->getRoles(),
            'customer_id' => $customer?->getId(),
        ];

        $event->setData($data);
    }
}
