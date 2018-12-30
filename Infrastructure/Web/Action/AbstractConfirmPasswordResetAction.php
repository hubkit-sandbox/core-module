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

use Closure;
use ParkManager\Module\CoreModule\Application\Service\Crypto\SplitTokenFactory;
use ParkManager\Module\CoreModule\Infrastructure\Web\Form\Handler\ServiceBusFormFactory;
use ParkManager\Module\CoreModule\Infrastructure\Web\Form\Security\ConfirmPasswordResetType;
use ParkManager\Module\CoreModule\Infrastructure\Web\TwigResponse;
use Rollerworks\Bundle\RouteAutofillBundle\Response\RouteRedirectResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractConfirmPasswordResetAction
{
    /** @var SplitTokenFactory */
    private $tokenFactory;

    public function __construct(SplitTokenFactory $tokenFactory)
    {
        $this->tokenFactory = $tokenFactory;
    }

    public function __invoke(Request $request, string $token, ServiceBusFormFactory $formFactory)
    {
        try {
            $splitToken = $this->tokenFactory->fromString($token);
        } catch (\Exception $e) {
            return new TwigResponse($this->getTemplate(), ['error' => 'password_reset.invalid_token'], 404);
        }

        $handler = $formFactory->createForQuery(ConfirmPasswordResetType::class, $this->createCommand(), ['token' => $splitToken]);
        $handler->handleRequest($request);

        if ($handler->isReady()) {
            return new RouteRedirectResponse($this->getLoginRoute());
        }

        $response = new TwigResponse($this->getTemplate(), $handler);
        $response->setPrivate();
        $response->setMaxAge(1);

        return $response;
    }

    abstract protected function getTemplate(): string;

    abstract protected function createCommand(): Closure;

    abstract protected function getLoginRoute(): string;
}
