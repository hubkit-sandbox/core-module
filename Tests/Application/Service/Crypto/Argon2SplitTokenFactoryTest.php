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
