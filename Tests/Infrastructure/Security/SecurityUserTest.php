<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\Security;

use ParkManager\Module\CoreModule\Infrastructure\Security\SecurityUser;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

/**
 * @internal
 */
final class SecurityUserTest extends TestCase
{
    private const ID1      = '930c3fd0-3bd1-11e7-bb9b-acdc32b58315';
    private const ID2      = 'c831846c-53f6-11e7-aceb-acbc32b58315';
    private const PASSWORD = 'my-password-is-better-then-your-password';

    /** @test */
    public function its_username_equals_UserId(): void
    {
        $securityUser  = $this->createSecurityUser();
        $securityUser2 = $this->createSecurityUser(self::ID2);

        self::assertSame(self::ID1, $securityUser->getUsername());
        self::assertSame(self::ID2, $securityUser2->getUsername());
        self::assertSame(self::ID1, $securityUser->getId());
        self::assertSame(self::ID2, $securityUser2->getId());
    }

    /** @test */
    public function its_password_is_equals_when_provided(): void
    {
        self::assertSame(self::PASSWORD, $this->createSecurityUser()->getPassword());
    }

    /** @test */
    public function its_password_is_empty_when_not_provided(): void
    {
        self::assertSame('', $this->createSecurityUser(self::ID1, null)->getPassword());
    }

    /** @test */
    public function it_has_roles(): void
    {
        self::assertSame(['ROLE_USER'], $this->createSecurityUser()->getRoles());
        self::assertSame(['ROLE_ADMIN'], $this->createSecurityUserSecond()->getRoles());
    }

    /** @test */
    public function it_equals_other_instance_with_same_information(): void
    {
        $securityUser1 = $this->createSecurityUser();
        $securityUser2 = $this->createSecurityUser();

        self::assertTrue($securityUser1->isEqualTo($securityUser2));
    }

    /** @test */
    public function it_does_not_equal_other_instance_with_different_information(): void
    {
        $securityUser1 = $this->createSecurityUser();
        $securityUser2 = $this->createSecurityUser(self::ID2); // id
        $securityUser3 = $this->createSecurityUser(self::ID1, 'ding-ding'); // password
        $securityUser4 = $this->createSecurityUserSecond(); // Different class
        $securityUser5 = new SecurityUserExtended(self::ID1, self::PASSWORD, true, ['ROLE_USER', 'ROLE_OPERATOR']); // Role
        $securityUser6 = new SecurityUserExtended(self::ID1, self::PASSWORD, false, ['ROLE_USER']); // Status

        self::assertFalse($securityUser1->isEqualTo($securityUser2), 'ID should must mismatch');
        self::assertFalse($securityUser1->isEqualTo($securityUser3), 'Password should must mismatch');
        self::assertFalse($securityUser1->isEqualTo($securityUser4), 'Class should be of same instance');
        self::assertFalse($securityUser1->isEqualTo($securityUser6), 'Enabled status should should mismatch');
        self::assertTrue($securityUser1->isEqualTo($securityUser5), 'Roles should should not mismatch');
    }

    /** @test */
    public function its_serializable(): void
    {
        $securityUser = new SecurityUserExtended(self::ID1, self::PASSWORD, false, ['ROLE_USER', 'ROLE_OPERATOR']);
        $unserialized = unserialize(serialize($securityUser), []);

        self::assertTrue($securityUser->isEqualTo($unserialized));
    }

    private function createSecurityUser(?string $id = self::ID1, ?string $password = self::PASSWORD): SecurityUser
    {
        return new SecurityUserExtended($id ?? self::ID1, (string) $password, true, ['ROLE_USER']);
    }

    private function createSecurityUserSecond(?string $id = self::ID2, ?string $password = self::PASSWORD): SecurityUser
    {
        return new SecurityUserSecond($id, (string) $password, true, ['ROLE_ADMIN']);
    }
}

final class SecurityUserExtended extends SecurityUser
{
}

final class SecurityUserSecond extends SecurityUser
{
}
