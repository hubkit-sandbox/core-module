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

namespace ParkManager\Module\CoreModule\Application\Command\User;

use ParkManager\Module\CoreModule\Domain\User\UserRepository;

final class ChangeUserPasswordHandler
{
    private $userCollection;

    public function __construct(UserRepository $userCollection)
    {
        $this->userCollection = $userCollection;
    }

    public function __invoke(ChangeUserPassword $command): void
    {
        $user = $this->userCollection->get($command->id());
        $user->changePassword($command->password());

        $this->userCollection->save($user);
    }
}
