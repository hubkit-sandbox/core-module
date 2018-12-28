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

namespace ParkManager\Module\CoreModule\Application\Command\Administrator;

use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;

final class ChangePassword
{
    /** @var AdministratorId */
    private $id;

    /** @var string|null */
    private $password;

    /**
     * @param string|null $password The password in hash-encoded format or null
     *                              to disable password based authentication
     */
    public function __construct(string $id, ?string $password)
    {
        $this->id       = AdministratorId::fromString($id);
        $this->password = $password;
    }

    public function id(): AdministratorId
    {
        return $this->id;
    }

    /**
     * @return string|null The password in hash-encoded format or null
     *                     to disable password based authentication
     */
    public function password(): ?string
    {
        return $this->password;
    }
}
