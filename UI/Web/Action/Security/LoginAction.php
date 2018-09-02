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

namespace ParkManager\Module\CoreModule\UI\Web\Action\Security;

use ParkManager\Bridge\Twig\Response\TwigResponse;
use ParkManager\Module\CoreModule\Infrastructure\Context\ApplicationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginAction
{
    private $authUtils;
    private $applicationContext;

    public function __construct(AuthenticationUtils $authUtils, ApplicationContext $applicationContext)
    {
        $this->authUtils = $authUtils;
        $this->applicationContext = $applicationContext;
    }

    public function __invoke(Request $request)
    {
        $error = $this->authUtils->getLastAuthenticationError();
        $lastUsername = $this->authUtils->getLastUsername();

        return new TwigResponse('@ParkManagerCore/security/login.html.twig', [
            'route' => 'park_manager.'.$this->applicationContext->getRouteNamePrefix().'.security_login',
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
