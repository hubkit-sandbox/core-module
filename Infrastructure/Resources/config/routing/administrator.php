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

namespace Symfony\Component\Routing\Loader\Configurator;

use ParkManager\Module\CoreModule\UI\Web\Action\HomepageAction;
use ParkManager\Module\CoreModule\UI\Web\Action\Security\{ConfirmPasswordResetAction,
    LoginAction,
    RequestPasswordResetAction,
    SecurityLogoutAction
};

return function (RoutingConfigurator $routes) {
    $admin = $routes->collection('park_manager.admin.');

        // Security
        $security = $admin->collection('security_');

        $security->add('login', '/login')
            ->controller(LoginAction::class)
            ->methods(['GET', 'POST']);

        $security->add('logout', '/logout')
            ->controller(SecurityLogoutAction::class)
            ->methods(['GET']);

        $security->add('request_password_reset', '/password-resetting')
            ->controller(RequestPasswordResetAction::class)
            ->methods(['GET', 'POST']);

        $security->add('confirm_password_reset', '/password-resetting/confirm/{token}')
            ->requirements(['token' => '.+']) // Token can contain slashes
            ->controller(ConfirmPasswordResetAction::class)
            ->methods(['GET', 'POST']);

    $admin->add('home', '/')->controller(HomepageAction::class);
    $admin->add('change_password', '/change-password')
        ->controller('park_manager.web_action.security.administrator.change_password')
        ->methods(['GET', 'POST']);
};
