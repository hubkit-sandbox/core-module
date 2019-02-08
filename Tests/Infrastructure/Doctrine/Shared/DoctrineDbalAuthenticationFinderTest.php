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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\Doctrine\Shared;

use ParkManager\Module\CoreModule\Application\Service\Finder\Shared\SecurityAuthenticationData;
use ParkManager\Module\CoreModule\Domain\Administrator\Administrator;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorRepository;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\Shared\DoctrineDbalAuthenticationFinder;
use ParkManager\Module\CoreModule\Test\Infrastructure\Doctrine\EntityRepositoryTestCase;

/**
 * @internal
 */
final class DoctrineDbalAuthenticationFinderTest extends EntityRepositoryTestCase
{
    private const EMAIL_1 = 'Today@rand.example.com';
    private const EMAIL_2 = 'Yyster@gand.example.com';

    private const ID2 = '22dcb406-2b9e-11e9-b8ae-acbc32b58315';
    private const ID1 = '1f0c0c0a-2b9e-11e9-8926-acbc32b58315';

    /** @var DoctrineDbalAuthenticationFinder */
    private $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->getEntityManager();
        $this->finder  = new DoctrineDbalAuthenticationFinder($em->getConnection(), 'administrator');

        /** @var AdministratorRepository $repo */
        $repo = self::$container->get('park_manager.repository.administrator');

        $repo->save(Administrator::register($id1 = AdministratorId::fromString(self::ID1), new EmailAddress(self::EMAIL_1), 'Janet Do'));
        $repo->save(Administrator::register($id2 = AdministratorId::fromString(self::ID2), new EmailAddress(self::EMAIL_2), 'Jared Moe', 'SomePassword'));

        $em->flush();
    }

    /** @test */
    public function it_returns_null_when_no_user_is_found()
    {
        self::assertNull($this->finder->findAuthenticationByEmail('today@rand.example.com')); // Result exists, but case mismatch
        self::assertNull($this->finder->findAuthenticationByEmail('nope-example.com'));
        self::assertNull($this->finder->findAuthenticationById('00000000-0000-0000-0000-000000000000'));
    }

    /** @test */
    public function it_returns_a_result_when_user_is_found()
    {
        $expected1 = new SecurityAuthenticationData(self::ID1, null, true, Administrator::DEFAULT_ROLES);
        $expected2 = new SecurityAuthenticationData(self::ID2, 'SomePassword', true, Administrator::DEFAULT_ROLES);

        self::assertEquals($expected1, $this->finder->findAuthenticationByEmail(self::EMAIL_1));
        self::assertEquals($expected1, $this->finder->findAuthenticationById(self::ID1));

        self::assertEquals($expected2, $this->finder->findAuthenticationByEmail(self::EMAIL_2));
        self::assertEquals($expected2, $this->finder->findAuthenticationById(self::ID2));
    }
}
