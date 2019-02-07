<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Command\Administrator;

use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;

final class RegisterAdministrator
{
    /** @var AdministratorId */
    private $id;

    /** @var EmailAddress */
    private $email;

    /** @var string */
    private $displayName;

    /** @var string|null */
    private $password;

    /**
     * @param string|null $password Null (no password) or an encoded password string (not plain)
     */
    public function __construct(string $id, string $email, string $displayName, ?string $password = null)
    {
        $this->id          = AdministratorId::fromString($id);
        $this->email       = new EmailAddress($email);
        $this->displayName = $displayName;
        $this->password    = $password;
    }

    public function id(): AdministratorId
    {
        return $this->id;
    }

    public function email(): EmailAddress
    {
        return $this->email;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function password(): ?string
    {
        return $this->password;
    }
}
