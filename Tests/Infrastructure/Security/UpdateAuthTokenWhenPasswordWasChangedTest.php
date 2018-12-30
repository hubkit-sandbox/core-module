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

use ParkManager\Module\CoreModule\Infrastructure\Event\UserPasswordWasChanged;
use ParkManager\Module\CoreModule\Infrastructure\Security\SecurityUser;
use ParkManager\Module\CoreModule\Infrastructure\Security\UpdateAuthTokenWhenPasswordWasChanged;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * @internal
 */
final class UpdateAuthTokenWhenPasswordWasChangedTest extends TestCase
{
    private const ID1 = '930c3fd0-3bd1-11e7-bb9b-acdc32b58315';
    private const ID2 = '930c3fd0-3bd1-11e7-bb9b-acdc32b58318';

    /** @test */
    public function it_ignores_when_no_token_was_set()
    {
        $userProvider = $this->createUserProvider();
        $tokenStorage = $this->createProvidingOnlyTokenStorage(null);

        $listener = new UpdateAuthTokenWhenPasswordWasChanged($userProvider, $tokenStorage);
        $listener->__invoke(new UserPasswordWasChanged(self::ID1));
    }

    private function createUserProvider(): UserProviderInterface
    {
        $userProviderProphecy = $this->prophesize(UserProviderInterface::class);
        $userProviderProphecy->refreshUser(Argument::any())->shouldNotBeCalled();

        return $userProviderProphecy->reveal();
    }

    private function createProvidingOnlyTokenStorage($token): TokenStorageInterface
    {
        $tokenStorageProphecy = $this->prophesize(TokenStorageInterface::class);
        $tokenStorageProphecy->getToken()->willReturn($token);
        $tokenStorageProphecy->setToken(Argument::any())->shouldNotBeCalled();

        return $tokenStorageProphecy->reveal();
    }

    /** @test */
    public function it_ignores_when_token_is_not_authenticated()
    {
        $userProvider = $this->createUserProvider();
        $token        = new PostAuthenticationGuardToken($this->createUser1(), 'main', ['ROLE_USER']);
        $token->setAuthenticated(false);

        $tokenStorage = $this->createProvidingOnlyTokenStorage($token);

        $listener = new UpdateAuthTokenWhenPasswordWasChanged($userProvider, $tokenStorage);
        $listener->__invoke(new UserPasswordWasChanged(self::ID1));
    }

    private function createUser1(): TestSecurityUser
    {
        return new TestSecurityUser(self::ID1, 'pass-north', true, ['ROLE_USER']);
    }

    /** @test */
    public function it_ignores_when_user_is_not_a_SecurityUser()
    {
        $userProvider = $this->createUserProvider();
        $tokenStorage = $this->createProvidingOnlyTokenStorage(new PostAuthenticationGuardToken($this->createMock(UserInterface::class), 'main', ['ROLE_USER']));

        $listener = new UpdateAuthTokenWhenPasswordWasChanged($userProvider, $tokenStorage);
        $listener->__invoke(new UserPasswordWasChanged(self::ID1));
    }

    /** @test */
    public function it_ignores_when_refreshed_user_is_not_enabled()
    {
        $currentUser  = $this->createUser1();
        $userProvider = $this->createUserProviderExpectsCurrentUser($currentUser, $this->createUser1Disabled());
        $tokenStorage = $this->createProvidingOnlyTokenStorage(new PostAuthenticationGuardToken($currentUser, 'main', ['ROLE_USER']));

        $listener = new UpdateAuthTokenWhenPasswordWasChanged($userProvider, $tokenStorage);
        $listener->__invoke(new UserPasswordWasChanged(self::ID1));
    }

    private function createUserProviderExpectsCurrentUser(TestSecurityUser $currentUser, TestSecurityUser $user): UserProviderInterface
    {
        $userProviderProphecy = $this->prophesize(UserProviderInterface::class);
        $userProviderProphecy->refreshUser($currentUser)->willReturn($user)->shouldBeCalled();

        return $userProviderProphecy->reveal();
    }

    private function createUser1Disabled(): TestSecurityUser
    {
        return new TestSecurityUser(self::ID1, 'pass-north', false, ['ROLE_USER']);
    }

    /** @test */
    public function it_only_updates_token_when_current_user()
    {
        $userProvider = $this->createUserProvider();
        $currentUser  = new TestSecurityUser(self::ID2, 'pass-north', true, ['ROLE_USER']);
        $tokenStorage = $this->createProvidingOnlyTokenStorage(new PostAuthenticationGuardToken($currentUser, 'main', ['ROLE_USER']));

        $listener = new UpdateAuthTokenWhenPasswordWasChanged($userProvider, $tokenStorage);
        $listener->__invoke(new UserPasswordWasChanged(self::ID1));
    }

    /** @test */
    public function it_marks_token_as_authenticated_and_sets_on_storage()
    {
        $token        = new PostAuthenticationGuardToken($currentUser = $this->createUser1(), 'main', ['ROLE_USER']);
        $userProvider = $this->createUserProviderExpectsCurrentUser($currentUser, $newUser = $this->createUser1());
        $tokenStorage = $this->createGetAndStoreTokenStorage($token);

        $listener = new UpdateAuthTokenWhenPasswordWasChanged($userProvider, $tokenStorage);
        $listener->__invoke(new UserPasswordWasChanged(self::ID1));

        self::assertTrue($token->isAuthenticated());
        self::assertSame($newUser, $token->getUser());
    }

    private function createGetAndStoreTokenStorage($token): TokenStorageInterface
    {
        $tokenStorageProphecy = $this->prophesize(TokenStorageInterface::class);
        $tokenStorageProphecy->getToken()->willReturn($token);
        $tokenStorageProphecy->setToken($token)->shouldBeCalled();

        return $tokenStorageProphecy->reveal();
    }
}

class TestSecurityUser extends SecurityUser
{
}
