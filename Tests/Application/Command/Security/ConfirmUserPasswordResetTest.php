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

namespace ParkManager\Module\CoreModule\Tests\Application\Command\Security;

use ParkManager\Component\Security\Token\FakeSplitTokenFactory;
use ParkManager\Module\CoreModule\Application\Command\Security\ConfirmUserPasswordReset;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ConfirmUserPasswordResetTest extends TestCase
{
    private const TOKEN_STRING = 'S1th74ywhDETYAaXWi-2Bee2_ltx-JPGKs9SVvbZCkMi8ZxiEVMBw68S';

    /** @test */
    public function its_constructable()
    {
        $password = 'my-password-I-forgot';
        $token = FakeSplitTokenFactory::instance()->fromString(self::TOKEN_STRING);

        $command = new ConfirmUserPasswordReset($token, $password);

        self::assertEquals($token, $command->token());
        self::assertEquals('my-password-I-forgot', $command->password());
    }
}
