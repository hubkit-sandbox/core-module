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

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Action\Client;

use ParkManager\Module\CoreModule\Application\Command\Client\RequestPasswordReset;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\Form\Handler\ServiceBusFormFactory;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\TwigResponse;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\RequestPasswordResetType;
use Rollerworks\Bundle\RouteAutofillBundle\Response\RouteRedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class RequestPasswordResetAction
{
    public function __invoke(Request $request, ServiceBusFormFactory $formFactory): object
    {
        $handler = $formFactory->createForCommand(RequestPasswordResetType::class, null, [
            'command_builder' => static function (string $email) {
                return new RequestPasswordReset($email);
            },
        ]);
        $handler->handleRequest($request);

        if ($handler->isReady()) {
            return new RouteRedirectResponse('park_manager.client.security_login');
        }

        $response = new TwigResponse('@ParkManagerCore/client/password_reset.html.twig', $handler);
        $response->setPrivate();
        $response->setMaxAge(1);

        return $response;
    }
}
