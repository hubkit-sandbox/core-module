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

use ParkManager\Component\ApplicationFoundation\Message\ServiceMessages;
use ParkManager\Component\Mailer\NullSender;
use ParkManager\Component\Mailer\Sender;
use ParkManager\Component\Security\Token\SodiumSplitTokenFactory;
use ParkManager\Component\Security\Token\SplitTokenFactory;
use ParkManager\Module\CoreModule\Infrastructure\Context\ApplicationContext;
use ParkManager\Module\CoreModule\Infrastructure\Context\SwitchableUserRepository;
use ParkManager\Module\CoreModule\Infrastructure\Http\ApplicationSectionListener;
use ParkManager\Module\CoreModule\Infrastructure\Http\SectionsLoader;
use ParkManager\Module\CoreModule\Infrastructure\Twig\AppContextGlobal;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()
        ->autowire()
        ->private();

    // ServiceBus ServiceMessages allow the service-bus to communicate non-critical messages
    // back to higher layers.
    $di->set('park_manager.service_bus.log_messages', ServiceMessages::class)
        ->alias(ServiceMessages::class, 'park_manager.service_bus.log_messages');

    $di->set(SodiumSplitTokenFactory::class)->alias(SplitTokenFactory::class, SodiumSplitTokenFactory::class);
    $di->set(NullSender::class)->alias(Sender::class, NullSender::class);

    $di->set(SwitchableUserRepository::class)->args([
        inline(ServiceLocator::class)
            ->tag('container.service_locator')
            ->arg(0, [
                'admin' => new ServiceClosureArgument(new Reference('park_manager.repository.administrator')),
                'private' => new ServiceClosureArgument(new Reference('park_manager.repository.administrator')),
                'client' => new ServiceClosureArgument(new Reference('park_manager.repository.administrator')),
            ]),
    ]);

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

    // Twig
    $di->set(AppContextGlobal::class);
};
