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

namespace ParkManager\Module\CoreModule\Tests\Application\Command\User;

use ParkManager\Module\CoreModule\Application\Command\User\EnableUser;
use ParkManager\Module\CoreModule\Application\Command\User\EnableUserHandler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EnableUserHandlerTest extends TestCase
{
    use UserCommandHandlerRepositoryTrait;

    /** @test */
    public function it_enables_user_access()
    {
        $handler = new EnableUserHandler($this->expectUserModelMethodCallAndSave('enable'));

        $command = new EnableUser(self::$userId);
        $handler($command);
    }
}
