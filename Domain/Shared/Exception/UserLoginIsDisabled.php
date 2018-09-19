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

namespace ParkManager\Module\CoreModule\Domain\Shared\Exception;

use ParkManager\Module\CoreModule\Domain\Shared\AbstractUserId;
use function sprintf;

final class UserLoginIsDisabled extends \DomainException
{
    public function __construct(AbstractUserId $id)
    {
        parent::__construct(sprintf('Login is disabled for user "%s".', $id));
    }
}
