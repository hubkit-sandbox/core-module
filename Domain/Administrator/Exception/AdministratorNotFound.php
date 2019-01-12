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

namespace ParkManager\Module\CoreModule\Domain\Administrator\Exception;

use InvalidArgumentException;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use function sprintf;

final class AdministratorNotFound extends InvalidArgumentException
{
    public static function withId(AdministratorId $id): self
    {
        return new self(sprintf('Administrator with id "%s" does not exist.', $id->toString()));
    }

    public static function withEmail(EmailAddress $email): self
    {
        return new self(sprintf('Administrator with email address "%s" does not exist.', $email->toString()));
    }
}
