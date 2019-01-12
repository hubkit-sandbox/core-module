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

namespace ParkManager\Module\CoreModule\Domain\Shared;

use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Domain\UuidTrait;

/**
 * OwnerId is used to "soft link" an entity to either a specific AbstractUser, or system.
 *
 * There are two special types: `internal` and `private`, which both use
 * a static id value to indicate there purpose.
 *
 * - Internal is managed by the system itself and used for platform configuration.
 *   Mainly the VirtualHost configuration of the hosting-management application
 *   is marked as `internal`, and reseller entry points.
 *
 * - Private marks the Entity is only accessible by Administrators, this used
 *   for corporate e-mail mailboxes and company owned websites.
 *
 * A `personal` id owner contains the AbstractUserId to realize a linkage between the
 * bounded contexts.
 *
 * Note: An Entity can only ever owned by a single user, not a group of users.
 */
final class OwnerId
{
    use UuidTrait;

    public const INTERNAL = '9667ac52-9038-11e8-b175-4a0003ae49a0';
    public const PRIVATE  = 'ae0efe1e-9038-11e8-9ebe-4a0003ae49a0';
    public const PERSONAL = 'personal';

    public static function internal(): self
    {
        return self::fromString(self::INTERNAL);
    }

    public static function private(): self
    {
        return self::fromString(self::PRIVATE);
    }

    public static function fromUserId(ClientId $id): self
    {
        return self::fromString($id->toString());
    }

    public function is(string $id): bool
    {
        if ($this->stringValue === $id) {
            return true;
        }

        return $id === self::PERSONAL && $this->stringValue !== self::INTERNAL && $this->stringValue !== self::PRIVATE;
    }
}
