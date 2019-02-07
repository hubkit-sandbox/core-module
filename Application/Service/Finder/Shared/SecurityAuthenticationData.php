<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Service\Finder\Shared;

final class SecurityAuthenticationData
{
    /**
     * READ-ONLY: The user-id in string format.
     *
     * @var string
     */
    public $id;

    /**
     * READ-ONLY.
     *
     * @var string|null
     */
    public $password;

    /**
     * READ-ONLY.
     *
     * @var bool
     */
    public $loginEnabled;

    /**
     * READ-ONLY.
     *
     * @var string[]
     */
    public $roles = [];

    public function __construct(string $id, ?string $password, bool $loginEnabled, array $roles)
    {
        $this->id           = $id;
        $this->password     = $password;
        $this->loginEnabled = $loginEnabled;
        $this->roles        = $roles;
    }
}
