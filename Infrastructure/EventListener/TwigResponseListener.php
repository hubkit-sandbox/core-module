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

namespace ParkManager\Module\CoreModule\Infrastructure\EventListener;

use ParkManager\Module\CoreModule\Infrastructure\Web\TwigResponse;
use Psr\Container\ContainerInterface as Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Environment;

/**
 * Handles a TwigResponse.
 *
 * The Twig Engine is lazily loaded as not every request invokes
 * the Twig engine (webservice API for example).
 */
final class TwigResponseListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        $response = $event->getResponse();

        if ($response instanceof TwigResponse) {
            $response->setRenderer($this->container->get('twig'));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -90], // Before ProfilerListener
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            'twig' => Environment::class,
        ];
    }
}
