<?php

namespace App\EventListener;

// Login and logout activity logging is handled exclusively by
// App\EventSubscriber\LoginLogoutSubscriber, which applies role-based
// filtering and deduplication. This listener is intentionally empty.
class AuthenticationEventListener
{
}