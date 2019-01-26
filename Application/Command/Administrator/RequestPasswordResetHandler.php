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

use DateTimeImmutable;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorRepository;
use ParkManager\Module\CoreModule\Domain\Administrator\Exception\AdministratorNotFound;
use Rollerworks\Component\SplitToken\SplitTokenFactory;

final class RequestPasswordResetHandler
{
    /** @var AdministratorRepository */
    private $repository;

    /** @var SplitTokenFactory */
    private $tokenFactory;

    /** @var int */
    private $tokenTTL;

    public function __construct(AdministratorRepository $Administrators, SplitTokenFactory $tokenFactory, int $tokenTTL = 3600)
    {
        $this->repository   = $Administrators;
        $this->tokenFactory = $tokenFactory;
        $this->tokenTTL     = $tokenTTL;
    }

    public function __invoke(RequestPasswordReset $command): void
    {
        // Create the token always to prevent leaking timing information,
        // when no administrator exists the token would have not been generated.
        // Thus leaking timing information about existence.
        //
        // It's still possible persistence may leak timing information
        // but leaking persistence timing is less risky.
        $splitToken = $this->tokenFactory->generate()->expireAt(
            new DateTimeImmutable('+ ' . $this->tokenTTL . ' seconds')
        );

        try {
            $administrator = $this->repository->getByEmail($command->email());
        } catch (AdministratorNotFound $e) {
            // No account with this e-mail address. To prevent exposing existence simply do nothing.
            return;
        }

        if ($administrator->requestPasswordReset($splitToken)) {
            $this->repository->save($administrator);
        }
    }
}
