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

namespace ParkManager\Module\CoreModule\Application\Command\Security;

use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;
use ParkManager\Module\CoreModule\Domain\User\Exception\PasswordResetConfirmationRejected;

final class ConfirmUserPasswordResetHandler
{
    private $userCollection;

    public function __construct(UserRepository $userCollection)
    {
        $this->userCollection = $userCollection;
    }

    public function __invoke(ConfirmUserPasswordReset $command): void
    {
        $token = $command->token();
        $user = $this->userCollection->getByPasswordResetToken($token->selector());
        $success = $user->confirmPasswordReset($token, $command->password());

        // Always save, as the token is cleared.
        $this->userCollection->save($user);

        if (!$success) {
            throw new PasswordResetConfirmationRejected();
        }
    }
}
