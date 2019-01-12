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

namespace ParkManager\Module\CoreModule\Application\Service\Crypto;

use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;

interface SplitTokenFactory
{
    /**
     * Generates a new SplitToken object.
     *
     * Example:
     *
     * ```
     * return SplitToken::create(
     *     new HiddenString(\random_bytes(SplitToken::TOKEN_CHAR_LENGTH), false, true), // DO NOT ENCODE HERE (always provide as raw binary)!
     *     $id
     * );
     * ```
     *
     *
     *
     * @see \ParagonIE\Halite\HiddenString
     * @return SplitToken
     */
    public function generate(): SplitToken;

    /**
     * Recreates a SplitToken object from a HiddenString (provided by eg. a user).
     *
     * Example:
     *
     * ```
     * return SplitToken::fromString($token);
     * ```
     */
    public function fromString(string $token): SplitToken;
}
