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
