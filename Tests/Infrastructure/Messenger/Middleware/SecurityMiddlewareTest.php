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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\Messenger\Middleware;

use ParkManager\Module\CoreModule\Infrastructure\Messenger\Middleware\SecurityMiddleware;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Test\Middleware\MiddlewareTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @internal
 */
final class SecurityMiddlewareTest extends MiddlewareTestCase
{
    /** @test */
    public function it_executed_next_middleware_when_granted()
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->expects(self::once())
            ->method('isGranted')
            ->with([], self::isInstanceOf(MockMessage::class))
            ->willReturn(true);

        $middleware = new SecurityMiddleware($authorizationChecker);

        $envelope = new Envelope(new MockMessage());
        self::assertSame($envelope, $middleware->handle($envelope, $this->getStackMock(true)));
    }

    /** @test */
    public function it_throws_access_denied_when_access_is_denied()
    {
        $envelope             = new Envelope(new MockMessage());
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->expects(self::once())
            ->method('isGranted')
            ->with([], self::isInstanceOf(MockMessage::class))
            ->willReturn(false);

        $middleware = new SecurityMiddleware($authorizationChecker);

        $this->expectException(AccessDeniedException::class);

        $middleware->handle($envelope, $this->getStackMock(false));
    }
}

class MockMessage
{
    public $id;
}
