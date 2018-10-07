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

namespace ParkManager\Module\CoreModule\Tests\Application\Command\Security;

use ParkManager\Module\CoreModule\Application\Command\Security\ConfirmUserPasswordReset;
use ParkManager\Module\CoreModule\Application\Command\Security\ConfirmUserPasswordResetHandler;
use ParkManager\Module\CoreModule\Domain\Shared\AbstractUser as User;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;
use ParkManager\Module\CoreModule\Domain\User\Exception\PasswordResetConfirmationRejected;
use ParkManager\Module\CoreModule\Test\Crypto\FakeSplitTokenFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @internal
 */
final class ConfirmUserPasswordResetHandlerTest extends TestCase
{
    public const TOKEN_STRING = 'S1th74ywhDETYAaXWi-2Bee2_ltx-JPGKs9SVvbZCkMi8ZxiEVMBw68S';
    public const SELECTOR     = 'S1th74ywhDETYAaXWi-2Bee2_ltx-JPG';

    private $token;

    protected function setUp(): void
    {
        $this->token = FakeSplitTokenFactory::instance()->fromString(self::TOKEN_STRING);
    }

    /** @test */
    public function it_handles_password_reset_confirmation()
    {
        $handler = new ConfirmUserPasswordResetHandler(
            $this->expectUserSaved(
                self::SELECTOR,
                $this->expectUserConfirmationTokenIsVerified($this->token, 'my-password')
            )
        );

        $command = new ConfirmUserPasswordReset($this->token, 'my-password');
        $handler($command);
    }

    /** @test */
    public function it_handles_password_reset_confirmation_with_failure()
    {
        $handler = new ConfirmUserPasswordResetHandler(
            $this->expectUserSaved(
                self::SELECTOR,
                $this->expectUserConfirmationTokenIsVerified($this->token, 'my-password', false)
            )
        );

        $this->expectException(PasswordResetConfirmationRejected::class);
        $handler(new ConfirmUserPasswordReset($this->token, 'my-password'));
    }

    /** @test */
    public function it_handles_password_reset_confirmation_with_no_result()
    {
        $handler = new ConfirmUserPasswordResetHandler($this->expectUserNotSaved());

        $this->expectException(PasswordResetTokenNotAccepted::class);
        $handler(new ConfirmUserPasswordReset(FakeSplitTokenFactory::instance()->fromString(self::TOKEN_STRING), 'my-password-word'));
    }

    private function expectUserConfirmationTokenIsVerified(SplitToken $token, string $password, bool $result = true): User
    {
        $userProphecy = $this->prophesize(User::class);
        $userProphecy->confirmPasswordReset($token, $password)->willReturn($result);

        return $userProphecy->reveal();
    }

    private function expectUserSaved(string $selector, User $user): UserRepository
    {
        $repositoryProphecy = $this->prophesize(UserRepository::class);
        $repositoryProphecy->getByPasswordResetToken($selector)->willReturn($user);
        $repositoryProphecy->save($user)->shouldBeCalledTimes(1);

        return $repositoryProphecy->reveal();
    }

    private function expectUserNotSaved(): UserRepository
    {
        $repositoryProphecy = $this->prophesize(UserRepository::class);
        $repositoryProphecy->getByPasswordResetToken(Argument::any())->willThrow(new PasswordResetTokenNotAccepted());
        $repositoryProphecy->save(Argument::any())->shouldNotBeCalled();

        return $repositoryProphecy->reveal();
    }
}
