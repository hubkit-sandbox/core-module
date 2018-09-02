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

use ParkManager\Module\CoreModule\Domain\User\UserId;

/**
 * ChangeUserPassword (with encoded-password).
 *
 * Note: For security reasons the password is provided in encoded format,
 */
final class ChangeUserPassword
{
    private $id;
    private $password;

    public function __construct(string $id, ?string $password)
    {
        $this->id = UserId::fromString($id);
        $this->password = $password;
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function password(): ?string
    {
        return $this->password;
    }
}
