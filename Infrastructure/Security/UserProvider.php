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

use ParkManager\Module\CoreModule\Domain\Administrator\Exception\AdministratorNotFound;
use ParkManager\Module\CoreModule\Domain\Shared\AbstractUser;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;
use ParkManager\Module\CoreModule\Domain\User\Exception\UserNotFound;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserProvider implements UserProviderInterface
{
    private $repository;
    private $userClass;

    public function __construct(UserRepository $repository, string $userClass)
    {
        $this->repository = $repository;
        $this->userClass = $userClass;

        if (!is_subclass_of($userClass, SecurityUser::class, true)) {
            throw new \InvalidArgumentException(
                sprintf('Expected UserClass (%s) to be a child of "%s"', $userClass, SecurityUser::class)
            );
        }
    }

    public function loadUserByUsername($username): SecurityUser
    {
        $user = $this->repository->findByEmailAddress(new EmailAddress($username));

        if (null === $user) {
            $e = new UsernameNotFoundException();
            $e->setUsername($username);

            throw $e;
        }

        return $this->createUser($user);
    }

    /**
     * @param SecurityUser $user
     *
     * @return SecurityUser
     */
    public function refreshUser(UserInterface $user): SecurityUser
    {
        if (!$user instanceof $this->userClass) {
            throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', $this->userClass, \get_class($user)));
        }

        try {
            $user = $this->repository->get($user->userId());
        } catch (AdministratorNotFound | UserNotFound $e) {
            $e = new UsernameNotFoundException();
            $e->setUsername($user->getUsername());

            throw $e;
        }

        return $this->createUser($user);
    }

    public function supportsClass($class): bool
    {
        return $this->userClass === $class;
    }

    private function createUser(AbstractUser $user): SecurityUser
    {
        return new $this->userClass($user->id()->toString(), (string) $user->password(), $user->isEnabled(), $user->roles());
    }
}
