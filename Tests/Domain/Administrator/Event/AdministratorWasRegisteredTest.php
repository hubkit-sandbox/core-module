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

namespace ParkManager\Module\CoreModule\Tests\Domain\Administrator\Event;

use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorWasRegistered;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AdministratorWasRegisteredTest extends TestCase
{
    private const USER_ID = '45a8ce38-5405-11e7-8853-acbc32b58315';

    /** @test */
    public function its_constructable()
    {
        $command = new AdministratorWasRegistered($id = AdministratorId::fromString(self::USER_ID), new EmailAddress('Jane@example.com'), 'First, Named');

        self::assertEquals(AdministratorId::fromString(self::USER_ID), $command->id());
        self::assertTrue($id->equals($command->id()));
        self::assertEquals('Jane@example.com', $command->email());
        self::assertEquals('First, Named', $command->displayName());
    }
}
