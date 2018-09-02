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

use ParkManager\Component\Security\Token\SplitTokenFactory;
use ParkManager\Module\CoreModule\Application\Service\EmailAddressChangeConfirmationMailer as ConfirmationMailer;
use ParkManager\Module\CoreModule\Domain\User\UserRepository;

final class RequestConfirmationOfEmailAddressChangeHandler
{
    private $userCollection;
    private $confirmationMailer;
    private $splitTokenFactory;
    private $tokenTTL;

    /**
     * @param UserRepository     $repository
     * @param ConfirmationMailer $mailer
     * @param SplitTokenFactory  $tokenFactory
     * @param int                $tokenTTL     Maximum life-time in seconds (default is 'one hour')
     */
    public function __construct(UserRepository $repository, ConfirmationMailer $mailer, SplitTokenFactory $tokenFactory, int $tokenTTL = 3600)
    {
        $this->tokenTTL = $tokenTTL;
        $this->userCollection = $repository;
        $this->splitTokenFactory = $tokenFactory;
        $this->confirmationMailer = $mailer;
    }

    public function __invoke(RequestConfirmationOfEmailAddressChange $command): void
    {
        $email = $command->email();

        if (null !== $this->userCollection->findByEmailAddress($email)) {
            // E-mail address is already in use by (another) user. To prevent exposing existence simply do nothing.
            // This also covers when the e-mail address was not actually changed.
            return;
        }

        $id = $command->id();
        $user = $this->userCollection->get($id);

        $tokenExpiration = new \DateTimeImmutable('+ '.$this->tokenTTL.' seconds');
        $splitToken = $this->splitTokenFactory->generate($id->toString(), $tokenExpiration);

        if ($user->setConfirmationOfEmailAddressChange($email, $splitToken->toValueHolder())) {
            $this->userCollection->save($user);
            $this->confirmationMailer->send($email, $splitToken, $tokenExpiration);
        }
    }
}
