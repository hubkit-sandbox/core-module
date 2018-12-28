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

namespace ParkManager\Module\CoreModule\Tests\Application\Command\Administrator;

use ParkManager\Module\CoreModule\Application\Command\Administrator\RegisterAdministrator;
use ParkManager\Module\CoreModule\Application\Command\Administrator\RegisterAdministratorHandler;
use ParkManager\Module\CoreModule\Domain\Administrator\Administrator;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorPasswordWasChanged;
use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorWasRegistered;
use ParkManager\Module\CoreModule\Domain\Administrator\Exception\AdministratorEmailAddressAlreadyInUse;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Test\Domain\Repository\AdministratorRepositoryMock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RegisterAdministratorHandlerTest extends TestCase
{
    private const ID_NEW      = '01dd5964-5426-11e7-be03-acbc32b58315';
    private const ID_EXISTING = 'a0816f44-6545-11e7-a234-acbc32b58315';

    /** @test */
    public function handle_registration_of_new_administrator()
    {
        $repo    = new AdministratorRepositoryMock();
        $handler = new RegisterAdministratorHandler($repo);

        $command = new RegisterAdministrator(self::ID_NEW, 'John@example.com', 'My', 'my-password');
        $handler($command);

        $repo->assertHasEntityWithEvents(
            self::ID_NEW,
            [
                new AdministratorWasRegistered($command->id(), $command->email(), $command->displayName()),
                new AdministratorPasswordWasChanged($command->id(), $command->password()),
            ]
        );
    }

    /** @test */
    public function handle_registration_without_password()
    {
        $repo    = new AdministratorRepositoryMock();
        $handler = new RegisterAdministratorHandler($repo);

        $command = new RegisterAdministrator(self::ID_NEW, 'John@example.com', 'My', null);
        $handler($command);

        $repo->assertHasEntityWithEvents(
            self::ID_NEW,
            [
                new AdministratorWasRegistered($command->id(), $command->email(), $command->displayName()),
            ]
        );
    }

    /** @test */
    public function handle_registration_of_new_user_with_already_existing_email()
    {
        $repo = new AdministratorRepositoryMock(
            [
                Administrator::register(
                    AdministratorId::fromString(self::ID_EXISTING),
                    new EmailAddress('John@example.com'),
                    'Jane'
                ),
            ]
        );
        $handler = new RegisterAdministratorHandler($repo);

        $this->expectException(AdministratorEmailAddressAlreadyInUse::class);

        $handler(new RegisterAdministrator(self::ID_NEW, 'John@example.com', 'My', null));
    }
}
