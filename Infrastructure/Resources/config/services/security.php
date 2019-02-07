<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ParkManager\Module\CoreModule\Application\Service\EventListener\ClientPasswordResetRequestListener;
use ParkManager\Module\CoreModule\Infrastructure\Security\AdministratorUser;
use ParkManager\Module\CoreModule\Infrastructure\Security\ClientUser;
use ParkManager\Module\CoreModule\Infrastructure\Security\EventListener\UserPasswordChangeListener;
use ParkManager\Module\CoreModule\Infrastructure\Security\FormAuthenticator;
use ParkManager\Module\CoreModule\Infrastructure\Security\UserProvider;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()
        ->autowire()
        ->autoconfigure(false)
        ->private();

    $di->set('park_manager.security.user_provider.administrator', UserProvider::class)
        ->args([ref('park_manager.repository.administrator'), AdministratorUser::class]);

    $di->set('park_manager.security.user_provider.client_user', UserProvider::class)
        ->args([ref('park_manager.repository.client_user'), ClientUser::class]);

    $di->set('park_manager.security.guard.form.administrator', FormAuthenticator::class)
        ->arg('$loginRoute', 'park_manager.admin.security_login')
        ->arg('$defaultSuccessRoute', 'park_manager.admin.home');

    $di->set('park_manager.security.guard.form.client', FormAuthenticator::class)
        ->arg('$loginRoute', 'park_manager.client.security_login')
        ->arg('$defaultSuccessRoute', 'home');

    $di->set(UserPasswordChangeListener::class)
        ->tag('messenger.message_handler', ['bus' => 'park_manager.event_bus']);

    // Client
    $di->set(ClientPasswordResetRequestListener::class)
        ->tag('messenger.message_handler', ['bus' => 'park_manager.event_bus']);
};
