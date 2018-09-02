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

use ParkManager\Module\CoreModule\Application\Command\User\DeleteUserRegistration;
use ParkManager\Module\CoreModule\Application\Command\User\DeleteUserRegistrationHandler;
use ParkManager\Module\CoreModule\Domain\User\User;
use ParkManager\Module\CoreModule\Domain\User\UserId;
use ParkManager\Module\CoreModule\Domain\User\UserRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DeleteUserRegistrationHandlerTest extends TestCase
{
    private const USER_ID = '45a8ce38-5405-11e7-8853-acbc32b58315';

    /** @test */
    public function it_deletes_a_user_registration()
    {
        $handler = new DeleteUserRegistrationHandler($this->expectUserIsDeletedFromRepository());

        $command = new DeleteUserRegistration(self::USER_ID);
        $handler($command);
    }

    private function expectUserIsDeletedFromRepository(): UserRepository
    {
        $userProphecy = $this->prophesize(User::class);
        $userProphecy->id()->willReturn(UserId::fromString(self::USER_ID));
        $user = $userProphecy->reveal();

        $repository = $this->prophesize(UserRepository::class);
        $repository->get($user->id())->willReturn($user);
        $repository->remove($user)->shouldBeCalled();

        return $repository->reveal();
    }
}
