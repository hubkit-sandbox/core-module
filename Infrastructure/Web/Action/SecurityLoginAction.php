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

namespace ParkManager\Module\CoreModule\Infrastructure\Web\Action;

use ParkManager\Module\CoreModule\Infrastructure\Context\ApplicationContext;
use ParkManager\Module\CoreModule\Infrastructure\Web\TwigResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityLoginAction
{
    public function __invoke(Request $request, AuthenticationUtils $authUtils, ApplicationContext $appContext): TwigResponse
    {
        return new TwigResponse('@ParkManagerCore/' . $appContext->getRouteNamePrefix() . '/login.html.twig', [
            'route' => 'park_manager.' . $appContext->getRouteNamePrefix() . '.security_login',
            'last_username' => $authUtils->getLastUsername(),
            'error' => $authUtils->getLastAuthenticationError(),
        ]);
    }
}
