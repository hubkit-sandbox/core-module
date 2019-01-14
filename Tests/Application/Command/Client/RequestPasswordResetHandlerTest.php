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

use DateTimeImmutable;
use ParkManager\Module\CoreModule\Application\Command\Client\RequestPasswordReset;
use ParkManager\Module\CoreModule\Application\Command\Client\RequestPasswordResetHandler;
use ParkManager\Module\CoreModule\Domain\Client\Client;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientPasswordResetWasRequested;
use ParkManager\Module\CoreModule\Test\Crypto\FakeSplitTokenFactory;
use ParkManager\Module\CoreModule\Test\Domain\Repository\ClientRepositoryMock;
use PHPUnit\Framework\TestCase;
use function array_pop;

/**
 * @internal
 */
final class RequestPasswordResetHandlerTest extends TestCase
{
    /** @var FakeSplitTokenFactory */
    private $tokenFactory;

    protected function setUp(): void
    {
        $this->tokenFactory = FakeSplitTokenFactory::instance();
    }

    /** @test */
    public function handle_reset_request(): void
    {
        $repository = new ClientRepositoryMock([$client = ClientRepositoryMock::createClient()]);

        $handler = new RequestPasswordResetHandler($repository, $this->tokenFactory, 120);
        $handler(new RequestPasswordReset('Jane@example.com'));

        $repository->assertHasEntity(
            $client->id(),
            static function (Client $entity) {
                $events = $entity->releaseEvents();

                self::assertCount(1, $events);

                /** @var ClientPasswordResetWasRequested $event */
                $event = array_pop($events);

                self::assertInstanceOf(ClientPasswordResetWasRequested::class, $event);

                $valueHolder = $event->token()->toValueHolder();
                self::assertFalse($valueHolder->isExpired(new DateTimeImmutable('+ 120 seconds')));
                self::assertTrue($valueHolder->isExpired(new DateTimeImmutable('+ 125 seconds')));
            }
        );
    }

    /** @test */
    public function reset_request_already_set_will_not_store(): void
    {
        $client = ClientRepositoryMock::createClient();
        $client->requestPasswordReset($this->tokenFactory->generate());
        $client->releaseEvents();
        $repository = new ClientRepositoryMock([$client]);

        $handler = new RequestPasswordResetHandler($repository, $this->tokenFactory);
        $handler(new RequestPasswordReset('Jane@example.com'));

        $repository->assertNoEntitiesWereSaved();
    }

    /** @test */
    public function reset_request_with_no_existing_email_does_nothing(): void
    {
        $repository = new ClientRepositoryMock();

        $handler = new RequestPasswordResetHandler($repository, $this->tokenFactory);
        $handler(new RequestPasswordReset('Jane@example.com'));

        $repository->assertNoEntitiesWereSaved();
    }
}
