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

namespace ParkManager\Module\CoreModule\Application\Query\Security;

use ParkManager\Module\CoreModule\Domain\Shared\AbstractUserId;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;

final class GetUserWithPasswordResetTokenHandler
{
    private $userFinder;

    public function __construct(UserRepository $passwordResetFinder)
    {
        $this->userFinder = $passwordResetFinder;
    }

    public function __invoke(GetUserByPasswordResetToken $query): AbstractUserId
    {
        $user = $this->userFinder->getByPasswordResetToken($query->token()->selector());
        $resetToken = $user->passwordResetToken();

        // Technically this value can still be null! And it helps static analyzers.
        if (null === $resetToken) {
            throw new PasswordResetTokenNotAccepted($resetToken, $query->token());
        }

        if (!$query->token()->matches($resetToken)) {
            throw new PasswordResetTokenNotAccepted($resetToken, $query->token());
        }

        return $user->id();
    }
}
