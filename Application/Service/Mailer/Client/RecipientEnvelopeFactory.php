<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Service\Mailer\Client;

use ParkManager\Module\CoreModule\Application\Service\Mailer\RecipientEnvelope;
use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;

interface RecipientEnvelopeFactory
{
    public function create(ClientId $id): RecipientEnvelope;

    public function createWith(ClientId $id, EmailAddress $email): RecipientEnvelope;
}
