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

namespace ParkManager\Module\CoreModule\Application\Command\Client;

use DateTimeImmutable;
use ParkManager\Module\CoreModule\Application\Service\Crypto\SplitTokenFactory;
use ParkManager\Module\CoreModule\Application\Service\Mailer\Client\EmailAddressChangeRequestMailer as ConfirmationMailer;
use ParkManager\Module\CoreModule\Domain\Client\ClientRepository;
use ParkManager\Module\CoreModule\Domain\Client\Exception\ClientNotFound;

final class RequestEmailAddressChangeHandler
{
    private $repository;
    private $confirmationMailer;
    private $splitTokenFactory;
    private $tokenTTL;

    /**
     * @param int $tokenTTL Maximum life-time in seconds (default is 'one hour')
     */
    public function __construct(ClientRepository $repository, ConfirmationMailer $mailer, SplitTokenFactory $tokenFactory, int $tokenTTL = 3600)
    {
        $this->tokenTTL           = $tokenTTL;
        $this->repository         = $repository;
        $this->splitTokenFactory  = $tokenFactory;
        $this->confirmationMailer = $mailer;
    }

    public function __invoke(RequestEmailAddressChange $command): void
    {
        $email = $command->email();

        try {
            $this->repository->getByEmail($email);

            // E-mail address is already in use by (another) client. To prevent exposing existence simply do nothing.
            // This also covers when the e-mail address was not actually changed.
            return;
        } catch (ClientNotFound $e) {
            // No-op
        }

        $id     = $command->id();
        $client = $this->repository->get($id);

        $tokenExpiration = new DateTimeImmutable('+ ' . $this->tokenTTL . ' seconds');
        $splitToken      = $this->splitTokenFactory->generate()->expireAt($tokenExpiration);

        if ($client->requestEmailChange($email, $splitToken)) {
            $this->repository->save($client);
            $this->confirmationMailer->send($id, $email, $splitToken, $tokenExpiration);
        }
    }
}
