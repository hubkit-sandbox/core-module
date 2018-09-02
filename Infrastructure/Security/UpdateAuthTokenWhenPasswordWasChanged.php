<?php

declare(strict_types=1);

/*
 * Copyright (c) the Contributors as noted in the AUTHORS file.
 *
 * This file is part of the Park-Manager project.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\Security;

use ParkManager\Component\DomainEvent\EventSubscriber;
use ParkManager\Module\CoreModule\Domain\User\Event\UserPasswordWasChanged;
use ParkManager\Module\CoreModule\Domain\User\UserId;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UpdateAuthTokenWhenPasswordWasChanged implements EventSubscriber
{
    private $userProvider;
    private $tokenStorage;

    public function __construct(UserProviderInterface $userProvider, TokenStorageInterface $tokenStorage)
    {
        $this->userProvider = $userProvider;
        $this->tokenStorage = $tokenStorage;
    }

    public function onUserPasswordWasChanged(UserPasswordWasChanged $event): void
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token || !$token->isAuthenticated() || !($user = $token->getUser()) instanceof SecurityUser) {
            return;
        }

        if (!$event->id()->equals(UserId::fromString($token->getUsername()))) {
            return;
        }

        /** @var SecurityUser $user */
        $user = $this->userProvider->refreshUser($user);

        if (!$user->isEnabled()) {
            return;
        }

        $token->setUser($user);
        $token->setAuthenticated(true); // User was changed, so re-mark authenticated.

        $this->tokenStorage->setToken($token);
    }

    public static function getSubscribedEvents(): array
    {
        return [UserPasswordWasChanged::class => 'onUserPasswordWasChanged'];
    }
}
