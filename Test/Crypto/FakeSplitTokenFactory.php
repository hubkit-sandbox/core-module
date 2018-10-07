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

namespace ParkManager\Module\CoreModule\Test\Crypto;

use ParagonIE\Halite\HiddenString;
use ParkManager\Module\CoreModule\Application\Service\Crypto\SplitTokenFactory;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use function hex2bin;
use function random_bytes;

/**
 * Always uses the same non-random value for the SplitToken to speed-up tests.
 *
 * !! THIS IMPLEMENTATION IS NOT SECURE, USE ONLY FOR TESTING !!
 */
final class FakeSplitTokenFactory implements SplitTokenFactory
{
    public const FULL_TOKEN = '1zUeXUvr4LKymANBB_bLEqiP5GPr-Pha_OR6OOnV1o8Vy_rWhDoxKNIt';

    private $randomValue;

    public static function instance(?string $randomValue = null): self
    {
        return new self($randomValue);
    }

    public static function randomInstance(): self
    {
        return new self(random_bytes(FakeSplitToken::TOKEN_DATA_LENGTH));
    }

    public function __construct(?string $randomValue = null)
    {
        $this->randomValue = $randomValue ?? hex2bin('d7351e5d4bebe0b2b298034107f6cb12a88fe463ebf8f85afce47a38e9d5d68f15cbfad6843a3128d22d');
    }

    public function generate(?string $id = null): SplitToken
    {
        return FakeSplitToken::create(new HiddenString($this->randomValue, false, true), $id);
    }

    public function fromString(string $token): SplitToken
    {
        return FakeSplitToken::fromString($token);
    }
}
