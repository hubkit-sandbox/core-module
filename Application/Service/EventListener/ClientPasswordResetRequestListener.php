<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Service\EventListener;

use ParkManager\Module\CoreModule\Application\Service\Mailer\Client\PasswordResetMailer;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientPasswordResetWasRequested;

final class ClientPasswordResetRequestListener
{
    /** @var PasswordResetMailer */
    private $mailer;

    public function __construct(PasswordResetMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(ClientPasswordResetWasRequested $event): void
    {
        $this->mailer->send(
            $event->id(),
            $event->token(),
            $event->token()->getExpirationTime()
        );
    }
}
