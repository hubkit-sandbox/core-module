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

use ParkManager\Module\CoreModule\Application\Command\Client\ConfirmEmailAddressChange;
use ParkManager\Module\CoreModule\Application\Command\Client\ConfirmEmailAddressChangeHandler;
use ParkManager\Module\CoreModule\Domain\Client\Client;
use ParkManager\Module\CoreModule\Domain\Client\Exception\EmailChangeConfirmationRejected;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use ParkManager\Module\CoreModule\Test\Crypto\FakeSplitTokenFactory;
use ParkManager\Module\CoreModule\Test\Domain\Repository\ClientRepositoryMock;
use PHPUnit\Framework\TestCase;
use function str_rot13;

/**
 * @internal
 */
final class ConfirmEmailAddressChangeHandlerTest extends TestCase
{
    /** @var SplitToken */
    private $fullToken;

    /** @var SplitToken */
    private $token;

    protected function setUp(): void
    {
        $this->fullToken = FakeSplitTokenFactory::instance()->generate();
        $this->token     = FakeSplitTokenFactory::instance()->fromString($this->fullToken->token()->getString());
    }

    /** @test */
    public function it_handles_emailAddress_change_confirmation()
    {
        $client = ClientRepositoryMock::createClient();
        $client->requestEmailChange(new EmailAddress('janet@example.com'), $this->fullToken);
        $repository = new ClientRepositoryMock([$client]);

        $handler = new ConfirmEmailAddressChangeHandler($repository);
        $handler(new ConfirmEmailAddressChange($this->token));

        $repository->assertEntitiesWereSaved();
        $repository->assertHasEntity(
            $client->id(),
            function (Client $entity) {
                self::assertEquals(new EmailAddress('janet@example.com'), $entity->email());
            }
        );
    }

    /** @test */
    public function it_handles_emailAddress_change_confirmation_with_failure()
    {
        $client = ClientRepositoryMock::createClient();
        $client->requestEmailChange(new EmailAddress('janet@example.com'), $this->fullToken);
        $repository = new ClientRepositoryMock([$client]);

        $handler = new ConfirmEmailAddressChangeHandler($repository);

        try {
            $invalidToken = FakeSplitTokenFactory::instance()->fromString(FakeSplitTokenFactory::SELECTOR . str_rot13(FakeSplitTokenFactory::VERIFIER));
            $handler(new ConfirmEmailAddressChange($invalidToken));

            $this->fail('Exception was expected.');
        } catch (EmailChangeConfirmationRejected $e) {
            $repository->assertEntitiesWereSaved();
            $repository->assertHasEntity(
                $client->id(),
                function (Client $entity) {
                    self::assertEquals(new EmailAddress('janE@example.com'), $entity->email());
                }
            );
        }
    }

    /** @test */
    public function it_handles_emailAddress_change_confirmation_with_no_result()
    {
        $client     = ClientRepositoryMock::createClient();
        $repository = new ClientRepositoryMock([$client]);

        $handler = new ConfirmEmailAddressChangeHandler($repository);

        try {
            $handler(new ConfirmEmailAddressChange(FakeSplitTokenFactory::instance('nananananananannnannanananannananna-batman')->generate()));

            $this->fail('Exception was expected.');
        } catch (EmailChangeConfirmationRejected $e) {
            $repository->assertNoEntitiesWereSaved();
        }
    }
}
