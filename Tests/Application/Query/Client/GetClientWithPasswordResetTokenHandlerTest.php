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

namespace ParkManager\Module\CoreModule\Tests\Application\Query\Client;

use ParkManager\Module\CoreModule\Application\Query\Client\GetClientWithPasswordResetToken;
use ParkManager\Module\CoreModule\Application\Query\Client\GetClientWithPasswordResetTokenHandler;
use ParkManager\Module\CoreModule\Domain\Client\Client;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use ParkManager\Module\CoreModule\Test\Crypto\FakeSplitTokenFactory;
use ParkManager\Module\CoreModule\Test\Domain\Repository\ClientRepositoryMock;
use PHPUnit\Framework\TestCase;
use function str_rot13;

/**
 * @internal
 */
final class GetClientWithPasswordResetTokenHandlerTest extends TestCase
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
    public function it_gets_clientId()
    {
        $client = ClientRepositoryMock::createClient();
        $client->requestPasswordReset($this->fullToken);
        $repository = new ClientRepositoryMock([$client]);

        $handler = new GetClientWithPasswordResetTokenHandler($repository);

        self::assertTrue($client->id()->equals($handler(new GetClientWithPasswordResetToken($this->token))));
        $repository->assertNoEntitiesWereSaved();
    }

    /** @test */
    public function it_clears_password_when_token_verifier_does_not_match()
    {
        $client = ClientRepositoryMock::createClient();
        $client->requestPasswordReset($this->fullToken);
        $repository = new ClientRepositoryMock([$client]);

        $handler = new GetClientWithPasswordResetTokenHandler($repository);

        try {
            $invalidToken = FakeSplitTokenFactory::instance()->fromString(FakeSplitTokenFactory::SELECTOR . str_rot13(FakeSplitTokenFactory::VERIFIER));
            $handler(new GetClientWithPasswordResetToken($invalidToken));
        } catch (PasswordResetTokenNotAccepted $e) {
            $repository->assertEntitiesWereSaved();
            $repository->assertHasEntity($client->id(), static function (Client $client) {
                self::assertEquals('', $client->passwordResetToken());
            });
        }
    }
}
