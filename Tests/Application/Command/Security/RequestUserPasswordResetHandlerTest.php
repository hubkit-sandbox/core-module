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

use ParkManager\Module\CoreModule\Test\Crypto\FakeSplitTokenFactory;
use ParkManager\Module\CoreModule\Application\Command\Security\RequestUserPasswordReset;
use ParkManager\Module\CoreModule\Application\Command\Security\RequestUserPasswordResetHandler;
use ParkManager\Module\CoreModule\Domain\Shared\AbstractUser as User;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;
use ParkManager\Module\CoreModule\Domain\User\UserId;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @internal
 */
final class RequestUserPasswordResetHandlerTest extends TestCase
{
    private const USER_ID = '01dd5964-5426-11e7-be03-acbc32b58315';

    /** @test */
    public function it_handles_password_reset_request()
    {
        $handler = new RequestUserPasswordResetHandler(
            $this->expectUserSaved(new EmailAddress('John2@example.com'), $this->expectUserConfirmationTokenIsSet()),
            FakeSplitTokenFactory::instance()
        );

        $command = new RequestUserPasswordReset('John2@example.com');
        $handler($command);
    }

    /** @test */
    public function it_handles_password_reset_request_with_token_already_set()
    {
        $handler = new RequestUserPasswordResetHandler(
            $this->expectUserNotSaved(new EmailAddress('John2@example.com'), $this->expectUserConfirmationTokenIsNotSet()),
            FakeSplitTokenFactory::instance()
        );

        $command = new RequestUserPasswordReset('John2@example.com');
        $handler($command);
    }

    /** @test */
    public function it_handles_password_reset_request_with_no_existing_emailAddress()
    {
        $handler = new RequestUserPasswordResetHandler(
            $this->expectUserNotSaved(new EmailAddress('John2@example.com'), null),
            FakeSplitTokenFactory::instance()
        );

        $command = new RequestUserPasswordReset('John2@example.com');
        $handler($command);
    }

    private function existingId(): UserId
    {
        return UserId::fromString(self::USER_ID);
    }

    private function expectUserConfirmationTokenIsSet(): User
    {
        $userProphecy = $this->prophesize(User::class);
        $userProphecy->id()->willReturn($this->existingId());
        $userProphecy->setPasswordResetToken(Argument::any())->willReturn(true);

        return $userProphecy->reveal();
    }

    private function expectUserConfirmationTokenIsNotSet(): User
    {
        $userProphecy = $this->prophesize(User::class);
        $userProphecy->id()->willReturn($this->existingId());
        $userProphecy->setPasswordResetToken(Argument::any())->willReturn(false);

        return $userProphecy->reveal();
    }

    private function expectUserNotSaved(EmailAddress $email, ?User $user): UserRepository
    {
        $repositoryProphecy = $this->prophesize(UserRepository::class);
        $repositoryProphecy->findByEmailAddress($email)->willReturn($user);
        $repositoryProphecy->save(Argument::any())->shouldNotBeCalled();

        return $repositoryProphecy->reveal();
    }

    private function expectUserSaved(EmailAddress $email, User $user): UserRepository
    {
        $repositoryProphecy = $this->prophesize(UserRepository::class);
        $repositoryProphecy->findByEmailAddress($email)->willReturn($user);
        $repositoryProphecy->save($user)->shouldBeCalledTimes(1);

        return $repositoryProphecy->reveal();
    }
}
