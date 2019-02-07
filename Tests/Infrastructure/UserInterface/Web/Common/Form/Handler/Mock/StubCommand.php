<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Common\Form\Handler\Mock;

final class StubCommand
{
    public $id;
    public $username;
    public $profile;

    public function __construct($id = 5, $username = null, $profile = null)
    {
        $this->id       = $id;
        $this->username = $username;
        $this->profile  = $profile;
    }
}
