<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Application\Query\Administrator;

use ParkManager\Module\CoreModule\Application\Query\Administrator\GetAdministratorWithPasswordResetToken;
use PHPUnit\Framework\TestCase;
use Rollerworks\Component\SplitToken\FakeSplitTokenFactory;

/**
 * @internal
 */
final class GetAdministratorByPasswordResetTokenTest extends TestCase
{
    /** @test */
    public function it_constructable(): void
    {
        $message = new GetAdministratorWithPasswordResetToken(
            $token = FakeSplitTokenFactory::instance()->fromString('S1th74ywhDETYAaXWi-2Bee2_ltx-JPGKs9SVvbZCkMi8ZxiEVMBw68S')
        );

        self::assertEquals($token, $message->token());
    }
}
