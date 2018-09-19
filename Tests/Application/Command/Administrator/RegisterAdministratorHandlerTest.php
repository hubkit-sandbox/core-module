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

namespace ParkManager\Module\CoreModule\Tests\Application\Command\Administrator;

use ParkManager\Module\CoreModule\Application\Command\Administrator\RegisterAdministrator;
use ParkManager\Module\CoreModule\Application\Command\Administrator\RegisterAdministratorHandler;
use ParkManager\Module\CoreModule\Domain\Administrator\Administrator;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorRepository;
use ParkManager\Module\CoreModule\Domain\Administrator\Exception\AdministratorEmailAddressAlreadyInUse;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @internal
 */
final class RegisterAdministratorHandlerTest extends TestCase
{
    private const ID_NEW      = '01dd5964-5426-11e7-be03-acbc32b58315';
    private const ID_EXISTING = 'a0816f44-6545-11e7-a234-acbc32b58315';

    /** @test */
    public function it_handles_registration_of_new_administrator()
    {
        $command = new RegisterAdministrator(self::ID_NEW, 'John@example.com', 'My', 'my-password');

        $handler = new RegisterAdministratorHandler($this->expectUserSaved($command));
        $handler($command);
    }

    /** @test */
    public function it_handles_registration_without_password()
    {
        $command = new RegisterAdministrator(self::ID_NEW, 'John@example.com', 'My', null);

        $handler = new RegisterAdministratorHandler($this->expectUserSaved($command));
        $handler($command);
    }

    /** @test */
    public function it_handles_registration_of_new_user_with_already_existing_email_address()
    {
        $command = new RegisterAdministrator(self::ID_NEW, 'John@example.com', 'My', null);

        $this->expectException(AdministratorEmailAddressAlreadyInUse::class);

        $handler = new RegisterAdministratorHandler($this->expectUserNotSaved($command->email()));
        $handler($command);
    }

    private function existingId(): AdministratorId
    {
        return AdministratorId::fromString(self::ID_EXISTING);
    }

    private function expectUserSaved(RegisterAdministrator $command): AdministratorRepository
    {
        $repository = $this->prophesize(AdministratorRepository::class);
        $repository->findByEmailAddress(Argument::any())->willReturn(null);
        $repository->save(Argument::that(function (Administrator $administrator) use ($command) {
            self::assertTrue($command->id()->equals($administrator->id()));
            self::assertEquals($command->email(), $administrator->email());
            self::assertEquals($command->password(), $administrator->password());
            self::assertEquals($command->displayName(), $administrator->displayName());

            return true;
        }))->shouldBeCalled();

        return $repository->reveal();
    }

    private function expectUserNotSaved(EmailAddress $email): AdministratorRepository
    {
        $adminProphecy = $this->prophesize(Administrator::class);
        $adminProphecy->id()->willReturn($this->existingId());

        $repositoryProphecy = $this->prophesize(AdministratorRepository::class);
        $repositoryProphecy->findByEmailAddress($email)->willReturn($adminProphecy->reveal());
        $repositoryProphecy->save(Argument::any())->shouldNotBeCalled();

        return $repositoryProphecy->reveal();
    }
}
