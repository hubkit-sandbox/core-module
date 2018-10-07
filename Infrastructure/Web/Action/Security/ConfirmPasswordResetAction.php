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

namespace ParkManager\Module\CoreModule\Infrastructure\Web\Action\Security;

use ParkManager\Module\CoreModule\Application\Query\Security\GetUserByPasswordResetToken;
use ParkManager\Module\CoreModule\Application\Service\Crypto\SplitTokenFactory;
use ParkManager\Module\CoreModule\Infrastructure\Web\Form\Handler\ServiceBusFormFactory;
use ParkManager\Module\CoreModule\Infrastructure\Web\Form\Security\ConfirmPasswordResetType;
use ParkManager\Module\CoreModule\Infrastructure\Web\TwigResponse;
use Rollerworks\Bundle\RouteAutofillBundle\Response\RouteRedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class ConfirmPasswordResetAction
{
    private $tokenFactory;
    private $formFactory;

    private $loginRoute;

    public function __construct(SplitTokenFactory $tokenFactory, ServiceBusFormFactory $formFactory)
    {
        $this->tokenFactory = $tokenFactory;
        $this->formFactory  = $formFactory;
    }

    public function __invoke(Request $request, string $token)
    {
        try {
            $splitToken = $this->tokenFactory->fromString($token);
        } catch (\Exception $e) {
            return new TwigResponse('@ParkManagerCore/security/password_reset_confirm.html.twig', ['error' => 'password_reset.invalid_token'], 404);
        }

        $handler = $this->formFactory->createForQuery(ConfirmPasswordResetType::class, new GetUserByPasswordResetToken($splitToken), ['token' => $splitToken]);
        $handler->handleRequest($request);

        if ($handler->isReady()) {
            return new RouteRedirectResponse($this->loginRoute);
        }

        $response = new TwigResponse('@ParkManagerCore/security/password_reset_confirm.html.twig', $handler);
        $response->setPrivate();
        $response->setMaxAge(1);

        return $response;
    }
}
