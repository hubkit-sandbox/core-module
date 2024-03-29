<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Application\Command\Client;

use ParkManager\Module\CoreModule\Application\Command\Client\DeleteRegistration;
use ParkManager\Module\CoreModule\Application\Command\Client\DeleteRegistrationHandler;
use ParkManager\Module\CoreModule\Test\Domain\Repository\ClientRepositoryMock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DeleteRegistrationHandlerTest extends TestCase
{
    /** @test */
    public function it_deletes_a_user_registration(): void
    {
        $repository = new ClientRepositoryMock([$client = ClientRepositoryMock::createClient()]);

        $handler = new DeleteRegistrationHandler($repository);
        $handler(new DeleteRegistration(ClientRepositoryMock::USER_ID1));

        $repository->assertEntitiesWereRemoved([$client]);
    }
}
