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

use ParkManager\Module\CoreModule\Infrastructure\Event\UserPasswordWasChanged;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Updates the current AuthenticationToken when the *current* user changes
 * their login password.
 */
final class UpdateAuthTokenWhenPasswordWasChanged
{
    private $userProvider;
    private $tokenStorage;

    public function __construct(UserProviderInterface $userProvider, TokenStorageInterface $tokenStorage)
    {
        $this->userProvider = $userProvider;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(UserPasswordWasChanged $event): void
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null || ! $token->isAuthenticated()) {
            return;
        }

        $user = $token->getUser();

        if (! $user instanceof SecurityUser) {
            return;
        }

        if ($event->getId() !== $token->getUsername()) {
            return;
        }

        /** @var SecurityUser $user */
        $user = $this->userProvider->refreshUser($user);

        if (! $user->isEnabled()) {
            return;
        }

        $token->setUser($user);
        $token->setAuthenticated(true); // User was changed, so re-mark authenticated.

        $this->tokenStorage->setToken($token);
    }
}
