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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\Security;

use InvalidArgumentException;
use ParkManager\Module\CoreModule\Application\Service\Finder\Client\AuthenticationFinder;
use ParkManager\Module\CoreModule\Application\Service\Finder\Shared\SecurityAuthenticationData;
use ParkManager\Module\CoreModule\Infrastructure\Security\AdministratorUser;
use ParkManager\Module\CoreModule\Infrastructure\Security\ClientUser;
use ParkManager\Module\CoreModule\Infrastructure\Security\UserProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @internal
 */
final class UserProviderTest extends TestCase
{
    /** @test */
    public function it_throws_fails_when_no_result_was_found(): void
    {
        $provider = new UserProvider($this->createNullFinderStub(), ClientUser::class);

        $this->expectException(UsernameNotFoundException::class);

        $provider->loadUserByUsername('foobar@example.com');
    }

    private function createNullFinderStub(): AuthenticationFinder
    {
        return new class() implements AuthenticationFinder {
            public function findAuthenticationByEmail(string $email): ?SecurityAuthenticationData
            {
                return null;
            }

            public function findAuthenticationById(string $id): ?SecurityAuthenticationData
            {
                return null;
            }
        };
    }

    /** @test */
    public function it_throws_fails_when_no_result_was_found_for_refreshing(): void
    {
        $provider = new UserProvider($this->createNullFinderStub(), ClientUser::class);

        $this->expectException(UsernameNotFoundException::class);

        $provider->refreshUser(new ClientUser('0', 'nope', true, []));
    }

    /** @test */
    public function it_checks_security_user_class_inheritance(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected UserClass (stdClass) to be a child of');

        new UserProvider($this->createNullFinderStub(), stdClass::class);
    }

    /** @test */
    public function it_supports_only_a_configured_class(): void
    {
        $provider = new UserProvider($this->createNullFinderStub(), ClientUser::class);

        self::assertTrue($provider->supportsClass(ClientUser::class));
        self::assertFalse($provider->supportsClass(AdministratorUser::class));
    }

    /** @test */
    public function it_provides_a_security_user(): void
    {
        $provider = new UserProvider($this->createSingleUserFinderStub(), ClientUser::class);

        self::assertEquals(new ClientUser('1', 'maybe', true, ['ROLE_USER']), $provider->loadUserByUsername('foobar@example.com'));
        self::assertEquals(new ClientUser('2', '', true, ['ROLE_USER']), $provider->loadUserByUsername('bar@example.com'));
        self::assertEquals(new ClientUser('3', 'nope', false, ['ROLE_USER']), $provider->loadUserByUsername('foo@example.com'));
        self::assertEquals(new ClientUser('4', 'nope', true, ['ROLE_USER', 'ROLE_RESELLER']), $provider->loadUserByUsername('moo@example.com'));
    }

    /** @test */
    public function it_refreshes_a_security_user(): void
    {
        $provider = new UserProvider($this->createSingleUserFinderStub(), ClientUser::class);

        self::assertEquals(new ClientUser('1', '', true, ['ROLE_USER2']), $provider->refreshUser($provider->loadUserByUsername('foobar@example.com')));
        self::assertEquals(new ClientUser('2', 'maybe', false, ['ROLE_USER2']), $provider->refreshUser($provider->loadUserByUsername('bar@example.com')));
        self::assertEquals(new ClientUser('3', 'nope2', true, ['ROLE_USER2']), $provider->refreshUser($provider->loadUserByUsername('foo@example.com')));
        self::assertEquals(new ClientUser('4', 'nope2', true, ['ROLE_USER2', 'ROLE_RESELLER2']), $provider->refreshUser($provider->loadUserByUsername('moo@example.com')));
    }

    private function createSingleUserFinderStub(): AuthenticationFinder
    {
        return new class() implements AuthenticationFinder {
            public function findAuthenticationByEmail(string $email): ?SecurityAuthenticationData
            {
                if ($email === 'foobar@example.com') {
                    return new SecurityAuthenticationData('1', 'maybe', true, ['ROLE_USER']);
                }

                if ($email === 'bar@example.com') {
                    return new SecurityAuthenticationData('2', null, true, ['ROLE_USER']);
                }

                if ($email === 'foo@example.com') {
                    return new SecurityAuthenticationData('3', 'nope', false, ['ROLE_USER']);
                }

                if ($email === 'moo@example.com') {
                    return new SecurityAuthenticationData('4', 'nope', true, ['ROLE_USER', 'ROLE_RESELLER']);
                }

                return null;
            }

            public function findAuthenticationById(string $id): ?SecurityAuthenticationData
            {
                if ($id === '1') {
                    return new SecurityAuthenticationData('1', null, true, ['ROLE_USER2']);
                }

                if ($id === '2') {
                    return new SecurityAuthenticationData('2', 'maybe', false, ['ROLE_USER2']);
                }

                if ($id === '3') {
                    return new SecurityAuthenticationData('3', 'nope2', true, ['ROLE_USER2']);
                }

                if ($id === '4') {
                    return new SecurityAuthenticationData('4', 'nope2', true, ['ROLE_USER2', 'ROLE_RESELLER2']);
                }

                return null;
            }
        };
    }
}
