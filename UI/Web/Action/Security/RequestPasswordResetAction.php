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
use ParkManager\Bundle\RouteAutofillBundle\Response\RouteRedirectResponse;
use ParkManager\Component\FormHandler\ServiceBusFormFactory;
use ParkManager\Module\CoreModule\Infrastructure\Context\ApplicationContext;
use ParkManager\Module\CoreModule\UI\Web\Form\Security\RequestPasswordResetType;
use Symfony\Component\HttpFoundation\Request;

final class RequestPasswordResetAction
{
    private $formFactory;
    private $applicationContext;

    public function __construct(ServiceBusFormFactory $formFactory, ApplicationContext $applicationContext)
    {
        $this->formFactory        = $formFactory;
        $this->applicationContext = $applicationContext;
    }

    public function __invoke(Request $request)
    {
        $handler = $this->formFactory->createForCommand(RequestPasswordResetType::class, null);
        $handler->handleRequest($request);

        if ($handler->isReady()) {
            return new RouteRedirectResponse('park_manager.' . $this->applicationContext->getRouteNamePrefix() . '.security_login');
        }

        $response = new TwigResponse('@ParkManagerCore/security/password_reset.html.twig', $handler);
        $response->setPrivate();
        $response->setMaxAge(1);

        return $response;
    }
}
