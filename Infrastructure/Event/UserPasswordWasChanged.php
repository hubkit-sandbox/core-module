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

namespace ParkManager\Module\CoreModule\Infrastructure\Event;

use Symfony\Component\EventDispatcher\Event;

final class UserPasswordWasChanged extends Event
{
    /** @var string */
    private $id;

    /** @var string|null */
    private $password;

    public function __construct(string $id, ?string $newPassword = null)
    {
        $this->id       = $id;
        $this->password = $newPassword;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNewPassword(): ?string
    {
        return $this->password;
    }
}
