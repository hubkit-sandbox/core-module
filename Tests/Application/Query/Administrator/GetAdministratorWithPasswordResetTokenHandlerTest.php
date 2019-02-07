<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Application\Query\Administrator;

use ParkManager\Module\CoreModule\Application\Query\Administrator\GetAdministratorWithPasswordResetToken;
use ParkManager\Module\CoreModule\Application\Query\Administrator\GetAdministratorWithPasswordResetTokenHandler;
use ParkManager\Module\CoreModule\Domain\Administrator\Administrator;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use ParkManager\Module\CoreModule\Test\Domain\Repository\AdministratorRepositoryMock;
use PHPUnit\Framework\TestCase;
use Rollerworks\Component\SplitToken\FakeSplitTokenFactory;
use Rollerworks\Component\SplitToken\SplitToken;
use function str_rot13;

/**
 * @internal
 */
final class GetAdministratorWithPasswordResetTokenHandlerTest extends TestCase
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
    public function it_gets_administratorId(): void
    {
        $administrator = AdministratorRepositoryMock::createAdministrator();
        $administrator->requestPasswordReset($this->fullToken);
        $repository = new AdministratorRepositoryMock([$administrator]);

        $handler = new GetAdministratorWithPasswordResetTokenHandler($repository);

        self::assertTrue($administrator->getId()->equals($handler(new GetAdministratorWithPasswordResetToken($this->token))));
        $repository->assertNoEntitiesWereSaved();
    }

    /** @test */
    public function it_clears_password_when_token_verifier_does_not_match(): void
    {
        $administrator = AdministratorRepositoryMock::createAdministrator();
        $administrator->requestPasswordReset($this->fullToken);
        $repository = new AdministratorRepositoryMock([$administrator]);

        $handler = new GetAdministratorWithPasswordResetTokenHandler($repository);

        try {
            $invalidToken = FakeSplitTokenFactory::instance()->fromString(FakeSplitTokenFactory::SELECTOR . str_rot13(FakeSplitTokenFactory::VERIFIER));
            $handler(new GetAdministratorWithPasswordResetToken($invalidToken));
        } catch (PasswordResetTokenNotAccepted $e) {
            $repository->assertEntitiesWereSaved();
            $repository->assertHasEntity($administrator->getId(), static function (Administrator $administrator) {
                self::assertEquals('', $administrator->getPasswordResetToken());
            });
        }
    }
}
