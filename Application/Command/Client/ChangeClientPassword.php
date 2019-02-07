<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Command\Client;

use ParkManager\Module\CoreModule\Domain\Client\ClientId;

final class ChangeClientPassword
{
    /** @var ClientId */
    private $id;

    /** @var string|null */
    private $password;

    /**
     * @param string|null $password The password in hash-encoded format or null
     *                              to disable password based authentication
     */
    public function __construct(string $id, ?string $password)
    {
        $this->id       = ClientId::fromString($id);
        $this->password = $password;
    }

    public function id(): ClientId
    {
        return $this->id;
    }

    /**
     * @return string|null The password in hash-encoded format or null
     *                     to disable password based authentication
     */
    public function password(): ?string
    {
        return $this->password;
    }
}
