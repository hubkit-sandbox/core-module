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

namespace ParkManager\Module\CoreModule\Tests\Application\Command\Client;

use ParkManager\Module\CoreModule\Application\Command\Client\ChangeClientPassword;
use ParkManager\Module\CoreModule\Application\Command\Client\ChangeClientPasswordHandler;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientPasswordWasChanged;
use ParkManager\Module\CoreModule\Test\Domain\Repository\ClientRepositoryMock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ChangeClientPasswordHandlerTest extends TestCase
{
    /** @test */
    public function it_changes_password(): void
    {
        $client     = ClientRepositoryMock::createClient();
        $repository = new ClientRepositoryMock([$client]);

        $handler = new ChangeClientPasswordHandler($repository);
        $handler(new ChangeClientPassword($client->id()->toString(), 'new-password'));

        $repository->assertEntitiesWereSaved();
        $repository->assertHasEntityWithEvents(
            $client->id(),
            [
                new ClientPasswordWasChanged($client->id(), 'new-password'),
            ]
        );
    }

    /** @test */
    public function it_changes_password_to_null(): void
    {
        $client     = ClientRepositoryMock::createClient();
        $repository = new ClientRepositoryMock([$client]);

        $handler = new ChangeClientPasswordHandler($repository);
        $handler(new ChangeClientPassword($client->id()->toString(), null));

        $repository->assertEntitiesWereSaved();
        $repository->assertHasEntityWithEvents(
            $client->id(),
            [
                new ClientPasswordWasChanged($client->id(), null),
            ]
        );
    }
}
