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

use ParkManager\Module\CoreModule\Application\Command\Client\RequestEmailAddressChange;
use ParkManager\Module\CoreModule\Application\Command\Client\RequestEmailAddressChangeHandler;
use ParkManager\Module\CoreModule\Application\Service\Mailer\Client\EmailAddressChangeRequestMailer;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientEmailAddressChangeWasRequested;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use ParkManager\Module\CoreModule\Test\Crypto\FakeSplitTokenFactory;
use ParkManager\Module\CoreModule\Test\Domain\Repository\ClientRepositoryMock;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @internal
 */
final class RequestEmailAddressChangeTest extends TestCase
{
    private const USER_ID = '01dd5964-5426-11e7-be03-acbc32b58315';

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
    public function it_handles_emailAddress_change_request()
    {
        $handler = new RequestEmailAddressChangeHandler(
            $repository = new ClientRepositoryMock([$client = ClientRepositoryMock::createClient()]),
            $this->createConfirmationMailer('John2@example.com'),
            FakeSplitTokenFactory::instance()
        );

        $handler(new RequestEmailAddressChange(self::USER_ID, 'John2@example.com'));

        $repository->assertEntitiesWereSaved();
        $repository->assertHasEntityWithEvents(
            $client->id(),
            [
                new ClientEmailAddressChangeWasRequested(
                    $client->id(),
                    FakeSplitTokenFactory::instance()->generate()->expireAt(new \DateTimeImmutable('+ 10 seconds')),
                    new EmailAddress('John2@example.com')
                ),
            ],
            function (ClientEmailAddressChangeWasRequested $expected, ClientEmailAddressChangeWasRequested $actual) {
                self::assertTrue($expected->id()->equals($actual->id()));
                self::assertTrue($expected->token()->equals($actual->token()));
                self::assertEquals($expected->getNewEmail(), $actual->getNewEmail());

                $token = $expected->token()->toValueHolder();
                self::assertFalse($token->isExpired(new \DateTimeImmutable('+ 5 seconds')));
                self::assertTrue($token->isExpired(new \DateTimeImmutable('+ 11 seconds')));
            }
        );
    }

    /** @test */
    public function it_handles_emailAddress_change_request_with_emailAddress_already_in_use()
    {
        $handler = new RequestEmailAddressChangeHandler(
            $repository = new ClientRepositoryMock([
                ClientRepositoryMock::createClient('janE@example.com'),
                $client2 = ClientRepositoryMock::createClient('John2@example.com'),
            ]),
            $this->expectNoConfirmationIsSendMailer(),
            FakeSplitTokenFactory::instance()
        );

        $handler(new RequestEmailAddressChange(self::USER_ID, 'John2@example.com'));

        $repository->assertNoEntitiesWereSaved();
    }

    private function createConfirmationMailer(string $email): \ParkManager\Module\CoreModule\Application\Service\Mailer\Client\EmailAddressChangeRequestMailer
    {
        $confirmationMailerProphecy = $this->prophesize(\ParkManager\Module\CoreModule\Application\Service\Mailer\Client\EmailAddressChangeRequestMailer::class);
        $confirmationMailerProphecy->send(
            $email,
            Argument::that(
                function (SplitToken $splitToken) {
                    return $splitToken->token()->getString() !== '';
                }
            ),
            Argument::any()
        )->shouldBeCalledTimes(1);

        return $confirmationMailerProphecy->reveal();
    }

    private function expectNoConfirmationIsSendMailer(): EmailAddressChangeRequestMailer
    {
        $confirmationMailerProphecy = $this->prophesize(\ParkManager\Module\CoreModule\Application\Service\Mailer\Client\EmailAddressChangeRequestMailer::class);
        $confirmationMailerProphecy->send(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

        return $confirmationMailerProphecy->reveal();
    }
}
