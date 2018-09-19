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

namespace ParkManager\Module\CoreModule\Tests\Domain\Mock;

use ParkManager\Module\CoreModule\Domain\EventsRecordingEntity;
use ParkManager\Module\CoreModule\Tests\Domain\Mock\Event\UserWasRegistered;

/** @internal */
final class User extends EventsRecordingEntity
{
    /** @var string */
    private $name;

    public static function register(string $name): self
    {
        $instance = new self();
        $instance->recordThat(new UserWasRegistered(1, $name));
        $instance->name = $name;

        return $instance;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function changeName(string $newName)
    {
        $this->name = $newName;
    }

    public function id()
    {
        return 1;
    }
}
