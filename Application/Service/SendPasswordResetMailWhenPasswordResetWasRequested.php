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

namespace ParkManager\Module\CoreModule\Application\Service;

use ParkManager\Component\DomainEvent\EventSubscriber;
use ParkManager\Module\CoreModule\Domain\Shared\Event\PasswordResetWasRequested;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;

/**
 * Listens for the {@link PasswordResetWasRequested} domain event and sends
 * a password-reset email (with the token) to the user if of the event.
 *
 * This listener stops propagation directly after sending.
 */
final class SendPasswordResetMailWhenPasswordResetWasRequested implements EventSubscriber
{
    private $mailer;
    private $userRepository;

    public function __construct(PasswordResetMailer $mailer, UserRepository $userRepository)
    {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
    }

    public function onPasswordResetWasRequested(PasswordResetWasRequested $event): void
    {
        $user = $this->userRepository->get($event->id());
        $this->mailer->send($user->email(), $event->token(), $user->passwordResetToken()->expiresAt());

        $event->stopPropagation();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PasswordResetWasRequested::class => 'onPasswordResetWasRequested',
        ];
    }
}
