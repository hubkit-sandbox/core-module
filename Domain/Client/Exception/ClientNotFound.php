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

namespace ParkManager\Module\CoreModule\Domain\Client\Exception;

use InvalidArgumentException;
use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use function sprintf;

final class ClientNotFound extends InvalidArgumentException
{
    public static function withId(ClientId $clientId): self
    {
        return new self(sprintf('ClientUser with id "%s" does not exist.', $clientId->toString()));
    }

    public static function withEmail(EmailAddress $address): self
    {
        return new self(sprintf('ClientUser with email "%s" does not exist.', $address->toString()));
    }
}
