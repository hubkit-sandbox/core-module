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

namespace ParkManager\Module\CoreModule\Domain\Client\Event;

use DateTimeImmutable;
use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;

final class ClientPasswordResetWasRequested
{
    /** @var ClientId */
    private $id;

    /** @var SplitToken */
    private $token;

    /**
     * READ ONLY.
     *
     * @var DateTimeImmutable
     */
    public $tokenExpiration;

    public function __construct(ClientId $id, SplitToken $token, DateTimeImmutable $tokenExpiration)
    {
        $this->id              = $id;
        $this->token           = $token;
        $this->tokenExpiration = $tokenExpiration;
    }

    public function id(): ClientId
    {
        return $this->id;
    }

    public function token(): SplitToken
    {
        return $this->token;
    }
}
