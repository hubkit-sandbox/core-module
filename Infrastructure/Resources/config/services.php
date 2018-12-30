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

use ParkManager\Module\CoreModule\Application\Service\Crypto\Argon2SplitTokenFactory;
use ParkManager\Module\CoreModule\Application\Service\Crypto\SplitTokenFactory;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorRepository;
use ParkManager\Module\CoreModule\Domain\Client\ClientRepository;
use ParkManager\Module\CoreModule\Infrastructure\Context\ApplicationContext;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\Administrator\DoctrineOrmAdministratorRepository;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\Client\DoctrineOrmClientRepository;
use ParkManager\Module\CoreModule\Infrastructure\Http\ApplicationSectionListener;
use ParkManager\Module\CoreModule\Infrastructure\Http\SectionsLoader;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()
        ->autowire()
        ->private()
        ->bind('$eventBus', ref('park_manager.event_bus'));

    $di->set(Argon2SplitTokenFactory::class)
        ->alias(SplitTokenFactory::class, Argon2SplitTokenFactory::class);

    $di->set('park_manager.repository.administrator', DoctrineOrmAdministratorRepository::class)
        ->alias(AdministratorRepository::class, 'park_manager.repository.administrator');

    $di->set('park_manager.repository.generic_user', DoctrineOrmClientRepository::class)
        ->alias(ClientRepository::class, 'park_manager.repository.generic_user');

    // RoutingLoader
    $di->set(SectionsLoader::class)
        ->tag('routing.loader')
        ->arg('$loader', ref('routing.resolver'))
        ->arg('$primaryHost', '%park_manager.config.primary_host%')
        ->arg('$isSecure', '%park_manager.config.is_secure%');

    $di->alias(ApplicationContext::class, 'park_manager.application_context');
    $di->set('park_manager.application_context', ApplicationContext::class);

    $di->set(ApplicationSectionListener::class)
        ->tag('kernel.event_subscriber')
        ->tag('kernel.reset', ['method' => 'reset'])
        ->arg('$sectionMatchers', [
            'admin' => ref('park_manager.section.admin.request_matcher'),
            'private' => ref('park_manager.section.private.request_matcher'),
            'client' => ref('park_manager.section.client.request_matcher'),
        ]);
};
