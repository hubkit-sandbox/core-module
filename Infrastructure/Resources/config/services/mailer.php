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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ParkManager\Module\CoreModule\Application\Service\Mailer\Client\EmailAddressChangeRequestMailer;
use ParkManager\Module\CoreModule\Application\Service\Mailer\Client\PasswordResetMailer;
use ParkManager\Module\CoreModule\Application\Service\Mailer\Client\RecipientEnvelopeFactory;
use ParkManager\Module\CoreModule\Infrastructure\Mailer\Client\ClientRecipientEnvelopeFactory;
use ParkManager\Module\CoreModule\Infrastructure\Mailer\Client\EmailAddressChangeRequestMailerImp as EmailAddressChangeConfirmationMailerImp;
use ParkManager\Module\CoreModule\Infrastructure\Mailer\Client\PasswordResetMailerImpl;
use ParkManager\Module\CoreModule\Infrastructure\Mailer\Sender\NullSender;
use ParkManager\Module\CoreModule\Infrastructure\Mailer\Sender\Sender;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()
        ->autowire()
        ->autoconfigure(false)
        ->private();

    $di->set(NullSender::class);
    $di->alias(Sender::class, NullSender::class);

    // Client
    $di->set(ClientRecipientEnvelopeFactory::class);
    $di->alias(RecipientEnvelopeFactory::class, ClientRecipientEnvelopeFactory::class);

    $di->set(PasswordResetMailerImpl::class);
    $di->alias(PasswordResetMailer::class, PasswordResetMailerImpl::class);

    $di->set(EmailAddressChangeConfirmationMailerImp::class);
    $di->alias(EmailAddressChangeRequestMailer::class, EmailAddressChangeConfirmationMailerImp::class);
};
