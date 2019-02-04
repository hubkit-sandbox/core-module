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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\EventListener;

use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\ApplicationContext;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\EventListener\ApplicationSectionListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @internal
 */
final class ApplicationSectionListenerTest extends TestCase
{
    /** @test */
    public function it_does_nothing_when_no_section_was_matched(): void
    {
        $listener = new ApplicationSectionListener(
            ['client' => new RequestMatcher('^/nope')],
            $this->doesNotExpectSectionIsSetContext()
        );

        $event = $this->createMock(GetResponseEvent::class);
        $event->expects(self::once())->method('isMasterRequest')->willReturn(true);
        $event->expects(self::any())->method('getRequest')->willReturn(new Request());

        $listener->onKernelRequest($event);
    }

    /** @test */
    public function it_does_nothing_when_not_in_master_request(): void
    {
        $listener = new ApplicationSectionListener(
            ['client' => new RequestMatcher('^/')],
            $this->doesNotExpectSectionIsSetContext()
        );

        $event = $this->createMock(GetResponseEvent::class);
        $event->expects(self::once())->method('isMasterRequest')->willReturn(false);
        $event->expects(self::never())->method('getRequest');

        $listener->onKernelRequest($event);
    }

    /** @test */
    public function it_sets_active_section_when_matched(): void
    {
        $listener = new ApplicationSectionListener(
            [
                'client' => new RequestMatcher('^/client/'),
                'admin' => new RequestMatcher('^/admin/'),
            ],
            $this->expectSectionIsSetContext('admin')
        );

        $event = $this->createMock(GetResponseEvent::class);
        $event->expects(self::once())->method('isMasterRequest')->willReturn(true);
        $event->expects(self::any())->method('getRequest')->willReturn(Request::create('/admin/'));

        $listener->onKernelRequest($event);
    }

    private function doesNotExpectSectionIsSetContext(): ApplicationContext
    {
        $contextProphecy = $this->prophesize(ApplicationContext::class);
        $contextProphecy->setActiveSection(Argument::any())->shouldNotBeCalled();

        return $contextProphecy->reveal();
    }

    private function expectSectionIsSetContext(string $section): ApplicationContext
    {
        $contextProphecy = $this->prophesize(ApplicationContext::class);
        $contextProphecy->setActiveSection($section)->shouldBeCalled();

        return $contextProphecy->reveal();
    }
}
