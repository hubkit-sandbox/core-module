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

namespace ParkManager\Module\CoreModule\Domain\User;

use ParkManager\Module\CoreModule\Domain\Shared\AbstractUser;
use ParkManager\Module\CoreModule\Domain\Shared\AbstractUserId;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository as BaseUserRepository;
use ParkManager\Module\CoreModule\Domain\User\Exception\UserNotFound;

interface UserRepository extends BaseUserRepository
{
    /**
     * @param UserId $id
     *
     * @throws UserNotFound when no user was found with the id
     *
     * @return User
     */
    public function get(AbstractUserId $id): User;

    public function findByEmailAddress(EmailAddress $email): ?User;

    /**
     * @param string $selector
     *
     * @throws UserNotFound when no user was found with the token selector
     *
     * @return User
     */
    public function getByEmailAddressChangeToken(string $selector): User;

    /**
     * @param string $selector
     *
     * @throws UserNotFound when no user was found with the token selector
     *
     * @return User
     */
    public function getByPasswordResetToken(string $selector): User;

    /**
     * Save the User in the repository.
     *
     * This will either store a new user or update an existing one.
     *
     * @param User $user
     */
    public function save(AbstractUser $user): void;

    /**
     * Remove a user registration from the repository.
     *
     * @param User $user
     */
    public function remove(User $user): void;
}
