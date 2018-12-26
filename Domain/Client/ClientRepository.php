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

namespace ParkManager\Module\CoreModule\Domain\Client;

use ParkManager\Module\CoreModule\Domain\Client\Exception\ClientNotFound;
use ParkManager\Module\CoreModule\Domain\Client\Exception\EmailChangeConfirmationRejected;
use ParkManager\Module\CoreModule\Domain\Client\Exception\PasswordResetConfirmationRejected;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;

interface ClientRepository
{
    /**
     * @throws ClientNotFound When no client was found with the id
     */
    public function get(ClientId $id): Client;

    /**
     * @throws ClientNotFound When no client was found with the email
     */
    public function getByEmail(EmailAddress $email): Client;

    /**
     * @throws PasswordResetConfirmationRejected When no client was found with the token-selector
     */
    public function getByPasswordResetToken(string $selector): Client;

    /**
     * @throws EmailChangeConfirmationRejected When no client was found with the token-selector
     */
    public function getByEmailAddressChangeToken(string $selector): Client;

    public function save(Client $client): void;

    public function remove(Client $client): void;
}
