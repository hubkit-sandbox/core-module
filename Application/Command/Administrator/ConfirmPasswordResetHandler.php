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

use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorRepository;
use ParkManager\Module\CoreModule\Domain\Administrator\Exception\PasswordResetConfirmationRejected;

final class ConfirmPasswordResetHandler
{
    /** @var AdministratorRepository */
    private $repository;

    public function __construct(AdministratorRepository $userCollection)
    {
        $this->repository = $userCollection;
    }

    public function __invoke(ConfirmPasswordReset $command): void
    {
        $token         = $command->token();
        $administrator = $this->repository->getByPasswordResetToken($token->selector());
        $exception     = null;

        // Cannot use finally here as the exception triggers the global exception handler
        // making the overall process unpredictable.

        try {
            $administrator->confirmPasswordReset($token, $command->password());
            $this->repository->save($administrator);
        } catch (PasswordResetConfirmationRejected $e) {
            $this->repository->save($administrator);

            throw $e;
        }
    }
}
