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

use Doctrine\ORM\EntityManagerInterface;
use League\Tactician\Plugins\LockingMiddleware;
use ParkManager\Bundle\ServiceBusBundle\DependencyInjection\Configurator\MessageBusConfigurator;
use ParkManager\Bundle\ServiceBusBundle\DependencyInjection\Configurator\QueryBusConfigurator;
use ParkManager\Component\ApplicationFoundation\Command\CommandBus;
//use ParkManager\Component\ApplicationFoundation\Query\QueryBus;
use ParkManager\Component\DomainEvent\EventEmitter;
use ParkManager\Component\Mailer\Sender;
use ParkManager\Module\CoreModule\Application\Service\PasswordResetMailer;
use ParkManager\Module\CoreModule\Application\Service\SendPasswordResetMailWhenPasswordResetWasRequested;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorRepository;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;
use ParkManager\Module\CoreModule\Infrastructure\Console\Command\RegisterAdministratorCommand;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\Administrator\DoctrineOrmAdministratorRepository;
use ParkManager\Module\CoreModule\Infrastructure\Mailer\PasswordResetSwiftMailer;
use ParkManager\Module\CoreModule\Infrastructure\Security\UpdateAuthTokenWhenPasswordWasChanged;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()
        ->autowire()
        // Bindings
        ->bind(CommandBus::class, ref('park_manager.command_bus.administrator'))
        //->bind(QueryBus::class, ref('park_manager.query_bus.administrator'))
        ->bind(EntityManagerInterface::class, ref('doctrine.orm.entity_manager'))
        ->bind(EventEmitter::class, ref('park_manager.command_bus.administrator.domain_event_emitter'))
        ->bind(AdministratorRepository::class, ref('park_manager.repository.administrator'))
        ->bind(UserRepository::class, ref('park_manager.repository.administrator'))
        ->bind(PasswordResetMailer::class, ref('park_manager.mailer.administrator_password_reset'))
        ->bind('$sender', ref(Sender::class))
    ;

    MessageBusConfigurator::register($di, 'park_manager.command_bus.administrator')
        ->middlewares()
            ->register(LockingMiddleware::class)
            ->doctrineOrmTransaction('default')
            ->domainEvents()
                ->subscriber(UpdateAuthTokenWhenPasswordWasChanged::class, [ref('park_manager.security.user_provider.administrator')])
                ->subscriber(SendPasswordResetMailWhenPasswordResetWasRequested::class)
            ->end()
        ->end()
        ->handlers(__DIR__ . '/../../../../Application/Command/Administrator')
            ->load('ParkManager\Module\CoreModule\Application\Command\Administrator\\', '*Handler.php')
        ->end();

    QueryBusConfigurator::register($di, 'park_manager.query_bus.administrator')
        ->handlers(__DIR__ . '/../../../../Application/Query')
        ->end();

    // Services
    $di->set('park_manager.repository.administrator', DoctrineOrmAdministratorRepository::class);
    $di->set('park_manager.mailer.administrator_password_reset', PasswordResetSwiftMailer::class)
        ->arg('$route', 'park_manager.admin.confirm_password_reset');

    // Actions

    // CliCommands
    $di->set(RegisterAdministratorCommand::class)
        ->tag('console.command', ['command' => 'park-manager:administrator:register']);
};
