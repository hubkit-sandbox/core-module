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

use ParkManager\Module\CoreModule\Application\Command\User\ChangeUserPassword;
use ParkManager\Module\CoreModule\Application\Command\User\ChangeUserPasswordHandler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @method prophesize($classOrInterface = null) : ObjectProphecy
 */
final class ChangeUserPasswordHandlerTest extends TestCase
{
    use UserCommandHandlerRepositoryTrait;

    /** @test */
    public function it_changes_password()
    {
        $handler = new ChangeUserPasswordHandler($this->expectUserModelMethodCallAndSave('changePassword', 'new-password'));

        $command = new ChangeUserPassword(self::$userId, 'new-password');
        $handler($command);
    }

    /** @test */
    public function it_changes_password_to_null()
    {
        $handler = new ChangeUserPasswordHandler($this->expectUserModelMethodCallAndSave('changePassword', null));

        $command = new ChangeUserPassword(self::$userId, null);
        $handler($command);
    }
}
