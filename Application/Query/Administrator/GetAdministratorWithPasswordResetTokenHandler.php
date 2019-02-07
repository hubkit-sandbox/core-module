<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Query\Administrator;

use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorRepository;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;

final class GetAdministratorWithPasswordResetTokenHandler
{
    private $repository;

    public function __construct(AdministratorRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(GetAdministratorWithPasswordResetToken $query): AdministratorId
    {
        $administrator = $this->repository->getByPasswordResetToken($query->token()->selector());
        $token         = $administrator->getPasswordResetToken();

        // If the token does not match force a removal of the token to prevent future guessing of the verifier.
        if (! $query->token()->matches($token)) {
            $administrator->clearPasswordReset();
            $this->repository->save($administrator);

            throw new PasswordResetTokenNotAccepted($token, $query->token());
        }

        return $administrator->getId();
    }
}
