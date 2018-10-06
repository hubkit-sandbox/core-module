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

use DateTimeImmutable;
use ParkManager\Module\CoreModule\Application\Service\Crypto\SplitTokenFactory;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;

final class RequestUserPasswordResetHandler
{
    private $userRepository;
    private $tokenFactory;
    private $tokenTTL;

    public function __construct(UserRepository $repository, SplitTokenFactory $tokenFactory, int $tokenTTL = 3600)
    {
        $this->userRepository = $repository;
        $this->tokenFactory   = $tokenFactory;
        $this->tokenTTL       = $tokenTTL;
    }

    public function __invoke(RequestUserPasswordReset $command): void
    {
        // Create the token always to prevent leaking timing information,
        // when no user exists the token would have not been generated.
        // Thus leaking timing information about existence.
        //
        // It's still possible persistence may leak timing information
        // but leaking persistence timing is less risky.

        $splitToken = $this->tokenFactory->generate()->expireAt(
            new DateTimeImmutable('+ ' . $this->tokenTTL . ' seconds')
        );

        $user = $this->userRepository->findByEmailAddress($command->email());

        if ($user === null) {
            // No account with this e-mail address. To prevent exposing existence simply do nothing.
            // It's still possible generating the token may leak timing information,
            return;
        }

        if ($user->setPasswordResetToken($splitToken->toValueHolder())) {
            $this->userRepository->save($user);
        }
    }
}
