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
use ParkManager\Module\CoreModule\Domain\Administrator\Exception\PasswordResetConfirmationRejected;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;

interface AdministratorRepository
{
    /**
     * @throws AdministratorNotFound When no administrator was found with the id
     */
    public function get(AdministratorId $id): Administrator;

    /**
     * @throws AdministratorNotFound When no administrator was found with the email
     */
    public function getByEmail(EmailAddress $email): Administrator;

    /**
     * @throws PasswordResetConfirmationRejected When no administrator was found with the token-selector
     */
    public function getByPasswordResetToken(string $selector): Administrator;

    public function save(Administrator $administrator): void;

    public function remove(Administrator $administrator): void;
}
