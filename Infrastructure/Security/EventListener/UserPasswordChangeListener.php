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

namespace ParkManager\Module\CoreModule\Infrastructure\Security\EventListener;

use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorPasswordWasChanged;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientPasswordWasChanged;
use ParkManager\Module\CoreModule\Infrastructure\Event\UserPasswordWasChanged;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as EventDispatcher;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface as MessageSubscriber;

final class UserPasswordChangeListener implements MessageSubscriber
{
    /** @var EventDispatcher */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ClientPasswordWasChanged|AdministratorPasswordWasChanged $message
     */
    public function __invoke(object $message): void
    {
        $this->eventDispatcher->dispatch(
            UserPasswordWasChanged::class,
            new UserPasswordWasChanged(
                $message->getId()->toString(),
                $message->getPassword()
            )
        );
    }

    public static function getHandledMessages(): iterable
    {
        yield ClientPasswordWasChanged::class;
        yield AdministratorPasswordWasChanged::class;
    }
}
