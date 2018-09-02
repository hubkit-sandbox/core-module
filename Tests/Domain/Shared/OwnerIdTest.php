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

namespace ParkManager\Module\CoreModule\Tests\Domain\Shared;

use ParkManager\Module\CoreModule\Domain\Shared\OwnerId;
use ParkManager\Module\CoreModule\Domain\User\UserId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class OwnerIdTest extends TestCase
{
    private const USER_ID = '783d3266-955a-11e8-8b48-4a0003ae49a0';

    /** @test */
    public function it_creates_from_userId()
    {
        $userId = UserId::fromString(self::USER_ID);
        $id = OwnerId::fromUserId($userId);

        self::assertTrue($id->equals(OwnerId::fromUserId(UserId::fromString(self::USER_ID))));
        self::assertFalse($id->equals(OwnerId::fromUserId(UserId::fromString('fb676f62-955a-11e8-8ef5-4a0003ae49a0'))));
        self::assertFalse($id->equals($userId));

        self::assertTrue($id->is(OwnerId::PERSONAL));
        self::assertFalse($id->is(OwnerId::INTERNAL));
        self::assertFalse($id->is(OwnerId::PRIVATE));
    }

    public function testInternal()
    {
        $id = OwnerId::internal();

        self::assertTrue($id->equals(OwnerId::internal()));
        self::assertFalse($id->equals(OwnerId::private()));
        self::assertFalse($id->equals(OwnerId::fromUserId(UserId::fromString(self::USER_ID))));

        self::assertTrue($id->is(OwnerId::INTERNAL));
        self::assertFalse($id->is(OwnerId::PRIVATE));
        self::assertFalse($id->is(OwnerId::PERSONAL));
    }

    public function testPrivate()
    {
        $id = OwnerId::private();

        self::assertTrue($id->equals(OwnerId::private()));
        self::assertFalse($id->equals(OwnerId::internal()));
        self::assertFalse($id->equals(OwnerId::fromUserId(UserId::fromString(self::USER_ID))));

        self::assertTrue($id->is(OwnerId::PRIVATE));
        self::assertFalse($id->is(OwnerId::INTERNAL));
        self::assertFalse($id->is(OwnerId::PERSONAL));
    }
}
