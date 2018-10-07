<?php

declare(strict_types=1);

/*
 * This file is part of the Park-Manager project.
 *
 * Copyright (c) the Contributors as noted in the AUTHORS file.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\Web;

use ParkManager\Module\CoreModule\Infrastructure\Web\TwigResponse;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TwigResponseTest extends TestCase
{
    /** @test */
    public function it_is_constructable()
    {
        $response = new TwigResponse('@CoreModule/client/show_user.html.twig', ['foo' => 'bar']);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('@CoreModule/client/show_user.html.twig', $response->getTemplate());
        self::assertSame(['foo' => 'bar'], $response->getTemplateVariables());
    }

    /** @test */
    public function it_is_constructable_with_a_custom_status_code()
    {
        $response = new TwigResponse('@CoreModule/client/show_user.html.twig', ['foo' => 'bar'], 400);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('@CoreModule/client/show_user.html.twig', $response->getTemplate());
        self::assertSame(['foo' => 'bar'], $response->getTemplateVariables());
    }

    /** @test */
    public function it_is_constructable_with_custom_headers()
    {
        $response = new TwigResponse('@CoreModule/client/show_user.html.twig', ['foo' => 'bar'], 200, ['X-Foo' => 'bar']);

        self::assertEquals('bar', $response->headers->get('X-Foo'));
    }
}
