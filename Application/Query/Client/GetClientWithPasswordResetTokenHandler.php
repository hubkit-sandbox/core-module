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

namespace ParkManager\Module\CoreModule\Application\Query\Client;

use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Domain\Client\ClientRepository;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;

final class GetClientWithPasswordResetTokenHandler
{
    /** @var ClientRepository */
    private $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(GetClientWithPasswordResetToken $query): ClientId
    {
        $client = $this->repository->getByPasswordResetToken($query->token()->selector());
        $token  = $client->passwordResetToken();

        // If the token does not match force a removal of the token to prevent future guessing of the verifier.
        if (! $query->token()->matches($token)) {
            $client->clearPasswordReset();
            $this->repository->save($client);

            throw new PasswordResetTokenNotAccepted($token, $query->token());
        }

        return $client->id();
    }
}
