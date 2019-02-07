<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Service\Mailer\Client;

use DateTimeImmutable;
use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use Rollerworks\Component\SplitToken\SplitToken;

interface EmailAddressChangeRequestMailer
{
    public function send(ClientId $id, EmailAddress $newAddress, SplitToken $splitToken, DateTimeImmutable $tokenExpiration): void;
}
