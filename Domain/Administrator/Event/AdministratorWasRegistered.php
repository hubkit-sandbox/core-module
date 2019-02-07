<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Domain\Administrator\Event;

use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;

final class AdministratorWasRegistered
{
    /** @var AdministratorId */
    private $id;

    /** @var EmailAddress */
    private $email;

    /** @var string */
    private $name;

    public function __construct(AdministratorId $id, EmailAddress $email, string $name)
    {
        $this->id    = $id;
        $this->email = $email;
        $this->name  = $name;
    }

    public function getId(): AdministratorId
    {
        return $this->id;
    }

    public function getEmail(): EmailAddress
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
