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

namespace ParkManager\Module\CoreModule\Application\Command\Administrator;

use ParkManager\Module\CoreModule\Domain\Administrator\Administrator;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorRepository;
use ParkManager\Module\CoreModule\Domain\Administrator\Exception\AdministratorEmailAddressAlreadyInUse;

final class RegisterAdministratorHandler
{
    private $repository;

    public function __construct(AdministratorRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(RegisterAdministrator $command): void
    {
        $email = $command->email();

        if (null !== $this->repository->findByEmailAddress($email)) {
            throw new AdministratorEmailAddressAlreadyInUse();
        }

        $this->repository->save(
            Administrator::registerWith($command->id(), $email, $command->displayName(), $command->password())
        );
    }
}
