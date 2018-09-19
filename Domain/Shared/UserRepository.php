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

namespace ParkManager\Module\CoreModule\Domain\Shared;

use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;

/**
 * UserRepository forms the basis for both the DefaultUser and Administrator
 * repository.
 *
 * This interface is an internal detail to allow reusing the Command/Query
 * and their handlers. Avoid type hinting against this interface directly.
 */
interface UserRepository
{
    /**
     * @throws PasswordResetTokenNotAccepted When no user is found with
     *                                       this split-token selector
     *
     * @return AbstractUser
     */
    public function getByPasswordResetToken(string $selector);

    /**
     * @return AbstractUser|null
     */
    public function findByEmailAddress(EmailAddress $email);

    /**
     * @return AbstractUser
     */
    public function get(AbstractUserId $id);

    /**
     * Save the user information in the repository.
     *
     * For "this" specific interface only updates are issued.
     */
    public function save(AbstractUser $user): void;
}
