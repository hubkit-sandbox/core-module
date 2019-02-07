<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ParkManager\Module\CoreModule\Infrastructure\Twig\AppContextGlobal;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\EventListener\TwigResponseListener;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()
        ->autowire()
        ->autoconfigure()
        ->private();

    $di->set(AppContextGlobal::class)
        ->args([ref('park_manager.application_context')]);

    $di->set(TwigResponseListener::class)
        ->tag('kernel.event_subscriber');
};
