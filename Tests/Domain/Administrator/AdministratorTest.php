<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Domain\Administrator;

use Assert\AssertionFailedException;
use DateTimeImmutable;
use ParkManager\Module\CoreModule\Domain\Administrator\Administrator;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorNameWasChanged;
use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorPasswordResetWasRequested;
use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorPasswordWasChanged;
use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorWasRegistered;
use ParkManager\Module\CoreModule\Domain\Administrator\Exception\CannotDisableSuperAdministrator;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Test\Domain\EventsRecordingEntityAssertionTrait;
use PHPUnit\Framework\TestCase;
use Rollerworks\Component\SplitToken\FakeSplitTokenFactory;
use Rollerworks\Component\SplitToken\SplitToken;
use function str_repeat;

/**
 * @internal
 */
final class AdministratorTest extends TestCase
{
    use EventsRecordingEntityAssertionTrait;

    private const ID1 = '930c3fd0-3bd1-11e7-bb9b-acdc32b58315';

    /** @var FakeSplitTokenFactory */
    private $splitTokenFactory;

    protected function setUp(): void
    {
        $this->splitTokenFactory = FakeSplitTokenFactory::instance();
    }

    public function testRegistered(): void
    {
        $user = Administrator::register($id = AdministratorId::fromString(self::ID1), $email = new EmailAddress('Jane@example.com'), 'Janet Doe');

        self::assertEquals($id, $user->getId());
        self::assertEquals($email, $user->getEmailAddress());
        self::assertTrue($user->isLoginEnabled());

        // Roles
        self::assertEquals(Administrator::DEFAULT_ROLES, $user->getRoles());
        self::assertTrue($user->hasRole('ROLE_ADMIN'));
        self::assertFalse($user->hasRole('ROLE_NOOP'));

        self::assertDomainEvents($user, [new AdministratorWasRegistered($id, $email, 'Janet Doe')]);
    }

    public function testChangeEmail(): void
    {
        $user = $this->registerAdministrator();
        $user->changeEmail($email = new EmailAddress('Doh@example.com'));

        self::assertEquals($email, $user->getEmailAddress());
    }

    public function testChangeDislayName(): void
    {
        $user = $this->registerAdministrator();
        $user->changeName('Jane Doe');

        self::assertDomainEvents($user, [new AdministratorNameWasChanged($user->getId(), 'Jane Doe')]);
    }

    public function testDisableAccess(): void
    {
        $user = $this->registerAdministrator();
        $user->disableLogin();

        self::assertFalse($user->isLoginEnabled());
    }

    public function testEnableAccessAfterDisabled(): void
    {
        $user = $this->registerAdministrator();
        $user->disableLogin();
        $user->enableLogin();

        self::assertTrue($user->isLoginEnabled());
    }

    public function testCannotDisableAccessWhenSuperAdmin(): void
    {
        $user = $this->registerAdministrator();
        $user->addRole('ROLE_SUPER_ADMIN');

        $this->expectException(CannotDisableSuperAdministrator::class);

        $user->disableLogin();
    }

    public function testChangePassword(): void
    {
        $user = $this->registerAdministrator();

        $user->changePassword('security-is-null');

        self::assertDomainEvents($user, [new AdministratorPasswordWasChanged($user->getId(), 'security-is-null')]);
    }

    public function testChangePasswordToNull(): void
    {
        $user = $this->registerAdministrator('security-is-null');
        $user->changePassword(null);

        self::assertDomainEvents($user, [new AdministratorPasswordWasChanged($user->getId(), null)]);
    }

    public function testPasswordCannotBeEmptyWhenString(): void
    {
        $user = $this->registerAdministrator();

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Password can only null or a non-empty string.');

        $user->changePassword('');
    }

    public function testAddRoles(): void
    {
        $user = $this->registerAdministrator();

        $user->addRole('ROLE_SUPER_ADMIN');
        $user->addRole('ROLE_SUPER_ADMIN'); // Ensure there're no duplicates

        self::assertEquals(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'], $user->getRoles());
        self::assertTrue($user->hasRole('ROLE_ADMIN'));
        self::assertTrue($user->hasRole('ROLE_SUPER_ADMIN'));
    }

    public function testRemoveRole(): void
    {
        $user = $this->registerAdministrator();
        $user->addRole('ROLE_SUPER_ADMIN');

        $user->removeRole('ROLE_SUPER_ADMIN');

        self::assertEquals(Administrator::DEFAULT_ROLES, $user->getRoles());
        self::assertTrue($user->hasRole('ROLE_ADMIN'));
        self::assertFalse($user->hasRole('ROLE_SUPER_ADMIN'));
    }

    public function testCannotRemoveDefaultRole(): void
    {
        $user = $this->registerAdministrator();

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Cannot remove default role "ROLE_ADMIN".');

        $user->removeRole('ROLE_ADMIN');
    }

    public function testRequestPasswordReset(): void
    {
        $token = $this->createTimeLimitedSplitToken(new DateTimeImmutable('+ 5 minutes UTC'));

        $user  = $this->registerAdministrator('pass-my-word');
        $user->requestPasswordReset($token);

        self::assertDomainEvents($user, [new AdministratorPasswordResetWasRequested($user->getId(), $token)]);
    }

    public function testChangesPasswordWhenTokenIsCorrect(): void
    {
        $token = $this->createTimeLimitedSplitToken(new DateTimeImmutable('+ 5 minutes UTC'));
        $user  = $this->registerAdministrator('pass-my-word');
        $id    = $user->getId();

        $user->requestPasswordReset($token);

        self::assertTrue($user->confirmPasswordReset($token2 = $this->getTokenString($token), 'new-password'));
        self::assertFalse($user->confirmPasswordReset($token2, 'new2-password'));
        self::assertDomainEvents(
            $user,
            [
                new AdministratorPasswordResetWasRequested($id, $token),
                new AdministratorPasswordWasChanged($id, 'new-password'),
            ]
        );
    }

    public function testPasswordResetIsRejectedForInvalidToken(): void
    {
        $correctToken = $this->createTimeLimitedSplitToken(new DateTimeImmutable('+ 5 minutes UTC'));
        $invalidToken = $this->generateSecondToken();

        $user  = $this->registerAdministrator('pass-my-word');
        $id    = $user->getId();

        $user->requestPasswordReset($correctToken);

        // Second attempt is prohibited, so try a second time (with correct token)!
        self::assertFalse($user->confirmPasswordReset($invalidToken, 'new-password'));
        self::assertFalse($user->confirmPasswordReset($correctToken, 'new-password'));
        self::assertDomainEvents(
            $user,
            [
                new AdministratorPasswordResetWasRequested($id, $correctToken),
            ]
        );
    }

    public function testPasswordResetIsRejectedWhenNoTokenWasSet(): void
    {
        $user  = $this->registerAdministrator('pass-my-word');

        self::assertFalse($user->confirmPasswordReset($this->splitTokenFactory->generate(), 'new-password'));
        self::assertNoDomainEvents($user);
    }

    /** @test */
    public function testPasswordResetIsRejectedWhenTokenHasExpired(): void
    {
        $token = $this->createTimeLimitedSplitToken(new DateTimeImmutable('- 5 minutes UTC'));
        $user  = $this->registerAdministrator('pass-my-word');
        $user->requestPasswordReset($token);

        self::assertFalse($user->confirmPasswordReset($token, 'new-password'));
        self::assertDomainEvents(
            $user,
            [new AdministratorPasswordResetWasRequested($user->getId(), $token)]
        );
    }

    private function registerAdministrator(?string $password = null): Administrator
    {
        $administrator = Administrator::register(
            $id = AdministratorId::fromString(self::ID1),
            $email = new EmailAddress('Jane@example.com'),
            'Janet Doe'
        );

        $administrator->changePassword($password);
        $administrator->releaseEvents();

        return $administrator;
    }

    private function getTokenString(SplitToken $token): SplitToken
    {
        return $this->splitTokenFactory->fromString($token->token()->getString());
    }

    private function createTimeLimitedSplitToken($expiresAt): SplitToken
    {
        return $this->splitTokenFactory->generate()->expireAt($expiresAt);
    }

    private function generateSecondToken(): SplitToken
    {
        return FakeSplitTokenFactory::instance(str_repeat('na', SplitToken::TOKEN_CHAR_LENGTH))->generate();
    }
}
