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

namespace ParkManager\Module\CoreModule\Application\Command\User;

use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\User\UserId;

final class RequestConfirmationOfEmailAddressChange
{
    private $id;
    private $email;

    public function __construct(string $id, string $email)
    {
        $this->id    = UserId::fromString($id);
        $this->email = new EmailAddress($email);
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): EmailAddress
    {
        return $this->email;
    }
}
