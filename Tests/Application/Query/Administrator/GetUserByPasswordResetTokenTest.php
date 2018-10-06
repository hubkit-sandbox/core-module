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

namespace ParkManager\Module\CoreModule\Tests\Application\Query\Administrator;

use ParkManager\Module\CoreModule\Test\Crypto\FakeSplitTokenFactory;
use ParkManager\Module\CoreModule\Application\Query\Security\GetUserByPasswordResetToken;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GetUserByPasswordResetTokenTest extends TestCase
{
    /** @test */
    public function it_constructable()
    {
        $message   = new GetUserByPasswordResetToken(
            $token = FakeSplitTokenFactory::instance()->fromString('S1th74ywhDETYAaXWi-2Bee2_ltx-JPGKs9SVvbZCkMi8ZxiEVMBw68S')
        );

        self::assertEquals($token, $message->token());
    }
}
