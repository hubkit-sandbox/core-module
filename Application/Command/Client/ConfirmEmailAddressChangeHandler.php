<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Command\Client;

use ParkManager\Module\CoreModule\Domain\Client\ClientRepository;
use ParkManager\Module\CoreModule\Domain\Client\Exception\EmailChangeConfirmationRejected;

final class ConfirmEmailAddressChangeHandler
{
    /** @var ClientRepository */
    private $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(ConfirmEmailAddressChange $command): void
    {
        $token     = $command->token();
        $client    = $this->repository->getByEmailAddressChangeToken($token->selector());
        $exception = null;

        // Cannot use finally here as the exception triggers the global exception handler
        // making the overall process unpredictable.

        try {
            $client->confirmEmailChange($token);
            $this->repository->save($client);
        } catch (EmailChangeConfirmationRejected $e) {
            $this->repository->save($client);

            throw $e;
        }
    }
}
