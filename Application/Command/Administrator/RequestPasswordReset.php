<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Command\Administrator;

use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;

final class RequestPasswordReset
{
    /** @var EmailAddress */
    private $email;

    public function __construct(string $email)
    {
        $this->email = new EmailAddress($email);
    }

    public function email(): EmailAddress
    {
        return $this->email;
    }
}
