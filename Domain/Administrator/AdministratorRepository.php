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

namespace ParkManager\Module\CoreModule\Domain\Administrator;

use ParkManager\Module\CoreModule\Domain\Administrator\Exception\AdministratorNotFound;
use ParkManager\Module\CoreModule\Domain\Shared\AbstractUser;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;

interface AdministratorRepository extends UserRepository
{
    /**
     * @param AdministratorId $id
     *
     * @throws AdministratorNotFound when no administrator was found with the id
     *
     * @return Administrator
     */
    public function get($id): Administrator;

    public function findByEmailAddress(EmailAddress $email): ?Administrator;

    public function getByPasswordResetToken(string $selector): Administrator;

    /**
     * Save the Administrator in the repository.
     *
     * This will either store a new Administrator registration
     * or update an existing one.
     *
     * @param Administrator $administrator
     */
    public function save(AbstractUser $administrator): void;

    /**
     * Remove an administrator registration from the repository.
     *
     * @param Administrator $administrator
     */
    public function remove(Administrator $administrator): void;
}
