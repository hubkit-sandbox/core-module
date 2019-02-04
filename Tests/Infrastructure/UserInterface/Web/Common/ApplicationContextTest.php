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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Common;

use InvalidArgumentException;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\ApplicationContext;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class ApplicationContextTest extends TestCase
{
    /** @test */
    public function it_throws_exception_for_unsupported_section(): void
    {
        $context = new ApplicationContext();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Section "frontend" is not supported.');

        $context->setActiveSection('frontend');
    }

    /**
     * @test
     * @dataProvider provideGetterMethods
     */
    public function it_throws_exception_when_calling_getter_with_an_uninitialised_context(string $method): void
    {
        $context = new ApplicationContext();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No active section was set.');

        $context->{$method}();
    }

    /**
     * @test
     * @dataProvider provideGetterMethods
     */
    public function it_throws_exception_when_calling_getter_after_resetting_context(string $method): void
    {
        $context = new ApplicationContext();
        $context->setActiveSection('client');

        $context->reset();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No active section was set.');

        $context->getActiveSection();
    }

    /**
     * @test
     * @dataProvider provideGetterMethods
     */
    public function it_get_active_section_info(): void
    {
        $context = new ApplicationContext();

        $context->setActiveSection('private');
        self::assertEquals('client', $context->getActiveSection());
        self::assertEquals('client', $context->getRouteNamePrefix());
        self::assertTrue($context->isPrivateSection());

        $context->setActiveSection('client');
        self::assertEquals('client', $context->getActiveSection());
        self::assertEquals('client', $context->getRouteNamePrefix());
        self::assertFalse($context->isPrivateSection());

        $context->setActiveSection('admin');
        self::assertEquals('admin', $context->getActiveSection());
        self::assertEquals('admin', $context->getRouteNamePrefix());
        self::assertFalse($context->isPrivateSection());
    }

    public function provideGetterMethods(): array
    {
        return [
            ['getActiveSection'],
            ['isPrivateSection'],
            ['getRouteNamePrefix'],
        ];
    }
}
