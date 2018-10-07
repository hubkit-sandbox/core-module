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

namespace ParkManager\Module\CoreModule\Tests\Application\Service\Crypto;

use ParkManager\Module\CoreModule\Application\Service\Crypto\Argon2SplitTokenFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class Argon2SplitTokenFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_a_new_token_on_every_call()
    {
        $factory     = new Argon2SplitTokenFactory();
        $splitToken1 = $factory->generate();
        $splitToken2 = $factory->generate();

        self::assertNotEquals($splitToken1->selector(), $splitToken2->selector());
        self::assertNotEquals($splitToken1, $splitToken2);
    }

    /**
     * @test
     */
    public function it_creates_from_string()
    {
        $factory              = new Argon2SplitTokenFactory();
        $splitToken           = $factory->generate();
        $fullToken            = $splitToken->token()->getString();
        $splitTokenFromString = $factory->fromString($fullToken);

        self::assertTrue($splitTokenFromString->matches($splitToken->toValueHolder()));
    }
}
