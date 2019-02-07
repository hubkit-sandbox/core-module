<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Domain\Client\Event;

use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;

final class ClientWasRegistered
{
    private $id;
    private $email;
    private $displayName;

    public function __construct(ClientId $id, EmailAddress $email, string $displayName)
    {
        $this->id          = $id;
        $this->email       = $email;
        $this->displayName = $displayName;
    }

    public function id(): ClientId
    {
        return $this->id;
    }

    public function email(): EmailAddress
    {
        return $this->email;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }
}
