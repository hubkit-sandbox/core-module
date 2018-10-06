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

namespace ParkManager\Module\CoreModule\Tests\Application\Command\User;

use ParkManager\Module\CoreModule\Test\Crypto\FakeSplitTokenFactory;
use ParkManager\Module\CoreModule\Application\Command\User\ConfirmEmailAddressChange;
use ParkManager\Module\CoreModule\Application\Command\User\ConfirmEmailAddressChangeHandler;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use ParkManager\Module\CoreModule\Domain\User\Exception\EmailChangeConfirmationRejected;
use ParkManager\Module\CoreModule\Domain\User\Exception\UserNotFound;
use ParkManager\Module\CoreModule\Domain\User\User;
use ParkManager\Module\CoreModule\Domain\User\UserRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @internal
 */
final class ConfirmEmailAddressChangeHandlerTest extends TestCase
{
    public const TOKEN_STRING = 'S1th74ywhDETYAaXWi-2Bee2_ltx-JPGKs9SVvbZCkMi8ZxiEVMBw68S';
    public const SELECTOR     = 'S1th74ywhDETYAaXWi-2Bee2_ltx-JPG';

    private $token;

    protected function setUp()
    {
        $this->token = FakeSplitTokenFactory::instance()->fromString(self::TOKEN_STRING);
    }

    /** @test */
    public function it_handles_emailAddress_change_confirmation()
    {
        $handler = new ConfirmEmailAddressChangeHandler(
            $this->expectUserSaved(
                self::SELECTOR,
                $this->expectUserConfirmationTokenIsVerified($this->token)
            )
        );

        $command = new ConfirmEmailAddressChange($this->token);
        $handler($command);
    }

    /** @test */
    public function it_handles_emailAddress_change_confirmation_with_failure()
    {
        $handler = new ConfirmEmailAddressChangeHandler(
            $this->expectUserSaved(
                self::SELECTOR,
                $this->expectUserConfirmationTokenIsVerified($this->token, false)
            )
        );

        $this->expectException(EmailChangeConfirmationRejected::class);
        $handler(new ConfirmEmailAddressChange($this->token));
    }

    /** @test */
    public function it_handles_emailAddress_change_confirmation_with_no_result()
    {
        $handler = new ConfirmEmailAddressChangeHandler($this->expectUserNotSaved());

        $this->expectException(UserNotFound::class);
        $handler(new ConfirmEmailAddressChange($this->token));
    }

    private function expectUserConfirmationTokenIsVerified(SplitToken $token, bool $result = true): User
    {
        $userProphecy = $this->prophesize(User::class);
        $userProphecy->confirmEmailAddressChange($token)->willReturn($result);

        return $userProphecy->reveal();
    }

    private function expectUserSaved(string $selector, User $user): UserRepository
    {
        $repositoryProphecy = $this->prophesize(UserRepository::class);
        $repositoryProphecy->getByEmailAddressChangeToken($selector)->willReturn($user);
        $repositoryProphecy->save($user)->shouldBeCalledTimes(1);

        return $repositoryProphecy->reveal();
    }

    private function expectUserNotSaved(): UserRepository
    {
        $repositoryProphecy = $this->prophesize(UserRepository::class);
        $repositoryProphecy->getByEmailAddressChangeToken(Argument::any())->willThrow(new UserNotFound());
        $repositoryProphecy->save(Argument::any())->shouldNotBeCalled();

        return $repositoryProphecy->reveal();
    }
}
