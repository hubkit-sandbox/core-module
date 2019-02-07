<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Command\Client;

use ParkManager\Module\CoreModule\Domain\Client\ClientRepository;

final class ChangeClientPasswordHandler
{
    private $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(ChangeClientPassword $command): void
    {
        $client = $this->repository->get($command->id());
        $client->changePassword($command->password());

        $this->repository->save($client);
    }
}
