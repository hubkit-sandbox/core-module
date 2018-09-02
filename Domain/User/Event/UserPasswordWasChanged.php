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

namespace ParkManager\Module\CoreModule\Domain\User\Event;

use ParkManager\Component\DomainEvent\DomainEvent;
use ParkManager\Module\CoreModule\Domain\Shared\AbstractUserId;

final class UserPasswordWasChanged extends DomainEvent
{
    private $id;

    public function __construct(AbstractUserId $id)
    {
        $this->id = $id;
    }

    public function id(): AbstractUserId
    {
        return $this->id;
    }
}
