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

use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;
use ParkManager\Module\CoreModule\Infrastructure\Context\SwitchableUserRepository;
use ParkManager\Module\CoreModule\Infrastructure\Messenger\Middleware\SecurityMiddleware;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()
        ->autowire()
        ->private()
        ->bind(UserRepository::class, ref(SwitchableUserRepository::class));

    $di->set('messenger.middleware.security', SecurityMiddleware::class)
        ->args([ref('security.authorization_checker')])
        ->abstract();

    $applicationDir = __DIR__ . '/../../../../Application/';
    $di->load('ParkManager\Module\CoreModule\Application\Command\\', $applicationDir . 'Command/**/*Handler.php')
        ->exclude(__DIR__ . '/../../../../Application/Command/User/{RequestConfirmationOfEmailAddressChangeHandler}.php')
        ->tag('messenger.bus', ['bus' => 'park_manager.command_bus']);

    $di->load('ParkManager\Module\CoreModule\Application\Query\\', $applicationDir . 'Query/**/*Handler.php')
        ->tag('messenger.bus', ['bus' => 'park_manager.query_bus']);
};
