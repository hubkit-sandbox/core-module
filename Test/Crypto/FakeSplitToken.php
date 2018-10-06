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

namespace ParkManager\Module\CoreModule\Test\Crypto;

use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use function sha1;

/**
 * !! THIS IMPLEMENTATION IS NOT SECURE, USE ONLY FOR TESTING !!
 */
final class FakeSplitToken extends SplitToken
{
    protected function verifyHash(string $hash, string $verifier): bool
    {
        $hashVerifier = $this->hashVerifier($verifier);

        return $hash === $hashVerifier;
    }

    protected function hashVerifier(string $verifier): string
    {
        return sha1($verifier);
    }
}
