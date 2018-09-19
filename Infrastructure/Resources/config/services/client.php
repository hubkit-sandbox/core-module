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
use ParkManager\Component\DomainEvent\EventEmitter;
use ParkManager\Component\Mailer\Sender;
use ParkManager\Module\CoreModule\Application\Service\EmailAddressChangeConfirmationMailer as EmailAddressChangeConfirmationMailerInterface;
use ParkManager\Module\CoreModule\Application\Service\PasswordResetMailer;
use ParkManager\Module\CoreModule\Application\Service\SendPasswordResetMailWhenPasswordResetWasRequested;
use ParkManager\Module\CoreModule\Domain\Shared\UserRepository;
use ParkManager\Module\CoreModule\Domain\User\UserRepository as GenericUserRepository;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\User\DoctrineOrmUserRepository;
use ParkManager\Module\CoreModule\Infrastructure\Mailer\EmailAddressChangeConfirmationMailer;
use ParkManager\Module\CoreModule\Infrastructure\Mailer\PasswordResetSwiftMailer;
use ParkManager\Module\CoreModule\Infrastructure\Security\UpdateAuthTokenWhenPasswordWasChanged;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()
        ->autowire()
        // Bindings
        //->bind(CommandBus::class, ref('park_manager.command_bus.generic_user'))
        //->bind(QueryBus::class, ref('park_manager.query_bus.generic_user'))
        ->bind(EntityManagerInterface::class, ref('doctrine.orm.entity_manager'))
        ->bind(EventEmitter::class, ref('park_manager.command_bus.generic_user.domain_event_emitter'))
        ->bind(GenericUserRepository::class, ref('park_manager.repository.generic_user'))
        ->bind(UserRepository::class, ref('park_manager.repository.generic_user'))
        ->bind(PasswordResetMailer::class, ref('park_manager.mailer.generic_user_password_reset'))
        ->bind(EmailAddressChangeConfirmationMailerInterface::class, ref('park_manager.mailer.generic_user_email_change'))
        ->bind('$sender', ref(Sender::class))
    ;

    MessageBusConfigurator::register($di, 'park_manager.command_bus.generic_user')
        ->middlewares()
            ->register(LockingMiddleware::class)
            ->doctrineOrmTransaction('default')
            ->domainEvents()
                ->subscriber(UpdateAuthTokenWhenPasswordWasChanged::class, [ref('park_manager.security.user_provider.generic_user')])
                ->subscriber(SendPasswordResetMailWhenPasswordResetWasRequested::class)
            ->end()
        ->end()
        ->handlers(__DIR__ . '/../../../../Application/Command/User')
            ->load('ParkManager\Module\CoreModule\Application\Command\User\\', '*Handler.php')
        ->end();

    QueryBusConfigurator::register($di, 'park_manager.query_bus.generic_user')
        ->handlers(__DIR__ . '/../../../../Application/Query')
            ->load('ParkManager\Module\CoreModule\Application\Query\User\\', '*Handler.php')
        ->end();

    // Services
    $di->set('park_manager.repository.generic_user', DoctrineOrmUserRepository::class);
    $di->set('park_manager.mailer.generic_user_password_reset', PasswordResetSwiftMailer::class)
        ->arg('$route', 'park_manager.generic_user.confirm_password_reset');

    $di->set('park_manager.mailer.generic_user_email_change', EmailAddressChangeConfirmationMailer::class)
        ->arg('$route', 'park_manager.generic_user.confirm_password_reset'); // XXX Fixme

    // Actions
};
