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

use ParkManager\Component\Mailer\NullSender;
use ParkManager\Component\Mailer\Sender;
use ParkManager\Module\CoreModule\Infrastructure\Mailer\ClientPasswordResetSwiftMailer;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()
        ->autowire()
        ->autoconfigure(false)
        ->private();

    $di->set(NullSender::class);
    $di->alias(Sender::class, NullSender::class);

    $di->set(ClientPasswordResetSwiftMailer::class);
};
