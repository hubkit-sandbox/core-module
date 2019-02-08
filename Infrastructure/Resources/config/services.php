<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ParkManager\Module\CoreModule\Infrastructure\Doctrine\Shared\DoctrineDbalAuthenticationFinder;
use Rollerworks\Component\SplitToken\Argon2SplitTokenFactory;
use ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\AutoServiceConfigurator;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\Administrator\DoctrineOrmAdministratorRepository;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\Client\DoctrineOrmClientRepository;
use ParkManager\Module\CoreModule\Infrastructure\Http\ArgumentResolver\ApplicationContextResolver;
use ParkManager\Module\CoreModule\Infrastructure\Http\ArgumentResolver\ServiceBusFormFactoryResolver;
use ParkManager\Module\CoreModule\Infrastructure\Http\SectionsLoader;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\ApplicationContext;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\EventListener\ApplicationSectionListener;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()
        ->autowire()
        ->private()
        ->bind('$eventBus', ref('park_manager.event_bus'));

    $autoDi = new AutoServiceConfigurator($di);

    $autoDi->set(Argon2SplitTokenFactory::class);
    $autoDi->set('park_manager.repository.administrator', DoctrineOrmAdministratorRepository::class);
    $autoDi->set('park_manager.repository.client_user', DoctrineOrmClientRepository::class);

    // Authentication finders
    $di->set('park_manager.query_finder.administrator', DoctrineDbalAuthenticationFinder::class)
        ->arg('$table', 'administrator');
    $di->set('park_manager.query_finder.client', DoctrineDbalAuthenticationFinder::class)
        ->arg('$table', 'client');

    // RoutingLoader
    $di->set(SectionsLoader::class)
        ->tag('routing.loader')
        ->arg('$loader', ref('routing.resolver'))
        ->arg('$primaryHost', '%park_manager.config.primary_host%')
        ->arg('$isSecure', '%park_manager.config.is_secure%');

    $autoDi->set('park_manager.application_context', ApplicationContext::class);

    $di->set(ApplicationSectionListener::class)
        ->tag('kernel.event_subscriber')
        ->tag('kernel.reset', ['method' => 'reset'])
        ->arg('$sectionMatchers', [
            'admin' => ref('park_manager.section.admin.request_matcher'),
            'private' => ref('park_manager.section.private.request_matcher'),
            'client' => ref('park_manager.section.client.request_matcher'),
        ]);

    $di->set(ServiceBusFormFactoryResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 30]);

    $di->set(ApplicationContextResolver::class)
        ->args([ref('park_manager.application_context')])
        ->tag('controller.argument_value_resolver', ['priority' => 30]);
};
