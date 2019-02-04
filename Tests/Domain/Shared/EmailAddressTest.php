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

namespace ParkManager\Module\CoreModule\Tests\Domain\Shared;

use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\MalformedEmailAddress;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EmailAddressTest extends TestCase
{
    /** @test */
    public function its_constructable(): void
    {
        $value = new EmailAddress('info@example.com');

        self::assertEquals('info@example.com', $value->address());
        self::assertEquals('info@example.com', $value->toString());
        self::assertEquals('info@example.com', $value->canonical());
        self::assertEquals('', $value->name());
        self::assertEquals('', $value->label());
    }

    /** @test */
    public function its_constructable_with_name(): void
    {
        $value = new EmailAddress('info@example.com', 'Janet Doe');

        self::assertEquals('info@example.com', $value->address());
        self::assertEquals('info@example.com', $value->canonical());
        self::assertEquals('Janet Doe', $value->name());
        self::assertEquals('', $value->label());
    }

    /** @test */
    public function it_canonicalizes_the_address(): void
    {
        $value = new EmailAddress('info@EXAMPLE.com');

        self::assertEquals('info@EXAMPLE.com', $value->address());
        self::assertEquals('info@example.com', $value->canonical());
        self::assertEquals('', $value->name());
        self::assertEquals('', $value->label());
    }

    /** @test */
    public function it_canonicalizes_the_address_with_idn(): void
    {
        $value = new EmailAddress('info@xn--tst-qla.de');

        // Note. Original value is not transformed as some IDN TLDs
        // are not supported natively (Emoji for example).
        self::assertEquals('info@xn--tst-qla.de', $value->address());
        self::assertEquals('info@täst.de', $value->canonical());
        self::assertEquals('', $value->name());
        self::assertEquals('', $value->label());
    }

    /** @test */
    public function it_extracts_the_label(): void
    {
        $value = new EmailAddress('info+hello@example.com');

        self::assertEquals('info+hello@example.com', $value->address());
        self::assertEquals('info@example.com', $value->canonical());
        self::assertEquals('', $value->name());
        self::assertEquals('hello', $value->label());
    }

    /** @test */
    public function it_validates_basic_formatting(): void
    {
        $this->expectException(MalformedEmailAddress::class);
        $this->expectExceptionMessage('Malformed e-mail address "info?example.com" (missing @)');

        new EmailAddress('info?example.com');
    }

    /** @test */
    public function it_validates_idn_format(): void
    {
        $this->expectException(MalformedEmailAddress::class);
        $this->expectExceptionMessageRegExp('/Malformed e-mail address "ok@xn--wat\.de" \(IDN Error reported \d+\)/');

        new EmailAddress('ok@xn--wat.de');
    }
}
