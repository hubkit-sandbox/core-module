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

use League\Tactician\Plugins\LockingMiddleware;
use ParkManager\Bundle\ServiceBusBundle\DependencyInjection\Configurator\MessageBusConfigurator;
use ParkManager\Bundle\ServiceBusBundle\DependencyInjection\Configurator\QueryBusConfigurator;
use ParkManager\Component\ApplicationFoundation\Command\CommandBus;
use ParkManager\Component\ApplicationFoundation\Query\QueryBus;
use ParkManager\Component\FormHandler\ServiceBusFormFactory;
use ParkManager\Module\CoreModule\Application\Command\Security\ConfirmUserPasswordResetHandler;
use ParkManager\Module\CoreModule\Application\Command\Security\RequestUserPasswordResetHandler;
use ParkManager\Module\CoreModule\Application\Query\Security\GetUserWithPasswordResetTokenHandler;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;
use ParkManager\Module\CoreModule\Infrastructure\Context\SwitchableUserRepository;
use ParkManager\Module\CoreModule\Infrastructure\Security\AdministratorUser;
use ParkManager\Module\CoreModule\Infrastructure\Security\FormAuthenticator;
use ParkManager\Module\CoreModule\Infrastructure\Security\GenericUser;
use ParkManager\Module\CoreModule\Infrastructure\Security\UserProvider;
use ParkManager\Module\CoreModule\UI\Web\Action\Security\ConfirmPasswordResetAction;
use ParkManager\Module\CoreModule\UI\Web\Action\Security\LoginAction;
use ParkManager\Module\CoreModule\UI\Web\Action\Security\RequestPasswordResetAction;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()
        ->autowire()
        ->private()
        // Bindings
        ->bind(CommandBus::class, ref('park_manager.command_bus.security'))
        ->bind(QueryBus::class, ref('park_manager.query_bus.security'))
        ->bind(ServiceBusFormFactory::class, ref('park_manager.form_handler.security'))
        ->bind(UserRepository::class, ref(SwitchableUserRepository::class))
    ;

    MessageBusConfigurator::register($di, 'park_manager.command_bus.security')
        ->middlewares()
            ->register(LockingMiddleware::class)
            ->doctrineOrmTransaction('default')
        ->end()
        ->handlers()
            ->register(RequestUserPasswordResetHandler::class)
            ->register(ConfirmUserPasswordResetHandler::class)
        ->end();

    QueryBusConfigurator::register($di, 'park_manager.query_bus.security')
        ->handlers()
            ->register(GetUserWithPasswordResetTokenHandler::class)
        ->end();

    // Services
    $di->set('park_manager.form_handler.security', ServiceBusFormFactory::class);

    // Actions
    $di->set(LoginAction::class)->public();
    $di->set(RequestPasswordResetAction::class)->public();
    $di->set(ConfirmPasswordResetAction::class)->public();

    $di->set('park_manager.security.user_provider.administrator', UserProvider::class)
        ->args([ref('park_manager.repository.administrator'), AdministratorUser::class])
        ->autoconfigure(true);

    $di->set('park_manager.security.user_provider.generic_user', UserProvider::class)
        ->args([ref('park_manager.repository.generic_user'), GenericUser::class])
        ->autoconfigure(false);

    $di->set('park_manager.security.guard.form.administrator', FormAuthenticator::class)
        ->arg('$loginRoute', 'park_manager.admin.security_login')
        ->arg('$defaultSuccessRoute', 'park_manager.admin.home');

    $di->set('park_manager.security.guard.form.client', FormAuthenticator::class)
        ->arg('$loginRoute', 'park_manager.client.security_login')
        ->arg('$defaultSuccessRoute', 'home');
};
