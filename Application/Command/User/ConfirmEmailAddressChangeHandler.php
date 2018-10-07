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

//use ParkManager\Module\CoreModule\Domain\User\Exception\EmailChangeConfirmationRejected;
//use ParkManager\Module\CoreModule\Domain\User\UserRepository;

final class ConfirmEmailAddressChangeHandler
{
    private $userCollection;

//    public function __construct(UserRepository $userCollection)
//    {
//        $this->userCollection = $userCollection;
//    }

    public function __invoke(ConfirmEmailAddressChange $command): void
    {
//        $token   = $command->token();
//        $user    = $this->userCollection->getByEmailAddressChangeToken($token->selector());
//        $success = $user->confirmEmailAddressChange($token);
//
//        // Always save, as the token is cleared.
//        //
//        // It's still possible the e-mail address was already 'assigned' to someone else.
//        // However this risk is rather small and handled by the repository constraints.
//        $this->userCollection->save($user);
//
//        if (! $success) {
//            throw new EmailChangeConfirmationRejected();
//        }
    }
}
