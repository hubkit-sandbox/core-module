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

use ParkManager\Module\CoreModule\Domain\DomainEventsCollectionTrait;
use ParkManager\Module\CoreModule\Domain\RecordsDomainEvents;

final class MockEntity implements RecordsDomainEvents
{
    use DomainEventsCollectionTrait;

    /** @var MockIdentity */
    private $id;

    /** @var string|null */
    public $name;

    private $lastName;

    public function __construct(string $id = 'fc86687e-0875-11e9-9701-acbc32b58315', string $name = 'Foobar')
    {
        $this->id       = MockIdentity::fromString($id);
        $this->lastName = $name;
    }

    public function id(): MockIdentity
    {
        return $this->id;
    }

    public function lastName()
    {
        return $this->lastName;
    }

    public function changeEmail(string $email)
    {
        $this->recordThat(new EmailChanged($this->id, $email));
    }
}
