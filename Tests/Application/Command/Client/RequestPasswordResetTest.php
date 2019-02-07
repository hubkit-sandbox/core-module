<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Application\Command\Client;

use ParkManager\Module\CoreModule\Application\Command\Client\RequestPasswordReset;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RequestPasswordResetTest extends TestCase
{
    /** @test */
    public function its_constructable(): void
    {
        $command = new RequestPasswordReset('jane@example.com');

        self::assertEquals('jane@example.com', $command->email());
    }
}
