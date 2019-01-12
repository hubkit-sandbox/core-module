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

use Serializable;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function get_class;
use function serialize;
use function unserialize;

/**
 * The SecurityUser wraps around a User-model and keeps only
 * the information related to authentication.
 *
 * To ensure password-encoders work properly this class must to be extended
 * for each each user-type (Client and Administrator).
 */
abstract class SecurityUser implements UserInterface, EquatableInterface, Serializable
{
    protected $username;
    protected $password;
    protected $roles;
    protected $enabled;

    public function __construct(string $id, string $password, bool $enabled, array $roles)
    {
        $this->username = $id;
        $this->password = $password;
        $this->enabled  = $enabled;
        $this->roles    = $roles;
    }

    public function serialize(): string
    {
        return serialize([
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'enabled' => $this->isEnabled(),
            'roles' => $this->getRoles(),
        ]);
    }

    public function unserialize($serialized): void
    {
        $data = unserialize($serialized, ['allowed_classes' => false]);

        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->enabled  = $data['enabled'];
        $this->roles    = $data['roles'];
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null; // No-op
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getId(): string
    {
        return $this->username;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function eraseCredentials(): void
    {
        // no-op
    }

    /**
     * @param static $user
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if (get_class($user) !== static::class) {
            return false;
        }

        // Should never mismatch, this is a safety precaution against a broken user-provider.
        if ($user->getUsername() !== $this->getUsername()) {
            return false;
        }

        if ($user->getPassword() !== $this->getPassword()) {
            return false;
        }

        /** @var static $user */
        return ! ($user->isEnabled() !== $this->isEnabled());
    }
}
