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

namespace ParkManager\Module\CoreModule\Application\Command\Administrator;

use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;

final class ConfirmPasswordReset
{
    /** @var SplitToken */
    private $token;

    /** @var string */
    private $password;

    /**
     * @param string $password The password provided in hash-encoded format
     */
    public function __construct(SplitToken $token, string $password)
    {
        $this->token    = $token;
        $this->password = $password;
    }

    public function token(): SplitToken
    {
        return $this->token;
    }

    public function password(): string
    {
        return $this->password;
    }
}
