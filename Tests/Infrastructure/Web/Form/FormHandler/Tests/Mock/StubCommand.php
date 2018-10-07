<?php

declare(strict_types=1);

/*
 * This file is part of the Park-Manager project.
 *
 * Copyright (c) the Contributors as noted in the AUTHORS file.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\Web\Form\FormHandler\Tests\Mock;

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
