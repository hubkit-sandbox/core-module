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
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RegisterAdministratorTest extends TestCase
{
    private const USER_ID = '45a8ce38-5405-11e7-8853-acbc32b58315';

    /** @test */
    public function its_constructable(): void
    {
        $command = new RegisterAdministrator(self::USER_ID, $email = 'John@example.com', 'First, Named', 'empty');

        self::assertEquals(AdministratorId::fromString(self::USER_ID), $command->id());
        self::assertEquals(new EmailAddress($email), $command->email());
        self::assertEquals('First, Named', $command->displayName());
        self::assertEquals('empty', $command->password());
    }

    /** @test */
    public function its_password_is_optional(): void
    {
        $command = new RegisterAdministrator(self::USER_ID, $email = 'John@example.com', 'First Named');

        self::assertEquals(AdministratorId::fromString(self::USER_ID), $command->id());
        self::assertEquals(new EmailAddress($email), $command->email());
        self::assertEquals('First Named', $command->displayName());
        self::assertNull($command->password());
    }
}
