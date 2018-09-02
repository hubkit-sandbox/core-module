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

use ParkManager\Module\CoreModule\Domain\User\Exception\UserNotFound;
use ParkManager\Module\CoreModule\Domain\User\UserRepository;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @internal
 *
 * @method ObjectProphecy prophesize($classOrInterface = null)
 */
trait UserCommandHandlerMissingUserTrait
{
    abstract public function it_fails_for_not_existing_user();

    protected function expectUserNotFoundWith(\Closure $handlerCreator, object $command): void
    {
        $repositoryProphecy = $this->prophesize(UserRepository::class);
        $repositoryProphecy->get($command->id())->willReturn(null);
        $handler = $handlerCreator($repositoryProphecy->reveal());

        $this->expectException(UserNotFound::class);
        $this->expectExceptionMessage(UserNotFound::withUserId($command->id())->getMessage());

        $handler($command);
    }
}
