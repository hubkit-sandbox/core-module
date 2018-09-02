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

namespace ParkManager\Module\CoreModule\Infrastructure\Context;

use ParkManager\Module\CoreModule\Domain\Shared\AbstractUser;
use ParkManager\Module\CoreModule\Domain\Shared\AbstractUserId;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;
use Psr\Container\ContainerInterface;

/**
 * Allow to switch the active user-repository at runtime.
 *
 * This Repository implementation is only to be used for the Symfony Security
 * sub-system.
 *
 * @final
 */
class SwitchableUserRepository implements UserRepository
{
    private $repositories;

    /**
     * @var UserRepository|null
     */
    private $repository;

    /**
     * @var string|null
     */
    private $active;

    public function __construct(ContainerInterface $repositories)
    {
        $this->repositories = $repositories;
    }

    public function setActive(?string $name): void
    {
        if (!$this->repositories->has($name)) {
            throw new \InvalidArgumentException(sprintf('Repository "%s" is not supported.', $name));
        }

        $this->active = $name;
        $this->repository = null;
    }

    public function reset()
    {
        $this->active = null;
        $this->repository = null;
    }

    public function getByPasswordResetToken(string $selector): AbstractUser
    {
        $this->guardRepositoryIsActive();

        return $this->repository->getByPasswordResetToken($selector);
    }

    public function findByEmailAddress(EmailAddress $email): ?AbstractUser
    {
        $this->guardRepositoryIsActive();

        return $this->repository->findByEmailAddress($email);
    }

    public function get(AbstractUserId $id): AbstractUser
    {
        $this->guardRepositoryIsActive();

        return $this->repository->get($id);
    }

    public function save(AbstractUser $user): void
    {
        $this->guardRepositoryIsActive();

        $this->repository->save($user);
    }

    private function guardRepositoryIsActive(): void
    {
        if (null === $this->active) {
            throw new \RuntimeException('Call setActive() before invoking any other method.');
        }

        $repository = $this->repositories->get($this->active);

        if (!($repository instanceof UserRepository)) {
            throw new \RuntimeException(sprintf('Repository "%s" service was expected to return a UserRepository instance.', $this->active));
        }

        $this->repository = $repository;
    }
}
