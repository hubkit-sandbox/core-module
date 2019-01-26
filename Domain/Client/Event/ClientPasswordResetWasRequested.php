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

use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use Rollerworks\Component\SplitToken\SplitToken;

final class ClientPasswordResetWasRequested
{
    /** @var ClientId */
    private $id;

    /** @var SplitToken */
    private $token;

    public function __construct(ClientId $id, SplitToken $token)
    {
        $this->id              = $id;
        $this->token           = $token;
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
