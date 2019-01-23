<?php

declare(strict_types=1);

/*
 * This file is part of the Park-Manager project.
 *
 * Copyright (c) the Contributors as noted in the AUTHORS file.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\EventListener;

use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\TwigResponse;
use Psr\Container\ContainerInterface as Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * The TwigResponseListener handles a TwigResponse.
 *
 * The Twig Engine is lazily loaded as not every request invokes
 * the Twig engine (webservice API for example).
 */
final class TwigResponseListener implements EventSubscriberInterface
{
    private $container;

    /**
     * @param Container $container Service container for loading *only* the Twig service (lazy)
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        $response = $event->getResponse();

        if (! $response instanceof TwigResponse || $response->isEmpty() || $response->getContent() !== '') {
            return;
        }

        // Note: This cannot be done different. Using a sendContent approach breaks the Profiler toolbar
        // as the content is set to late.

        $response->setContent(
            $this->container->get('twig')->render($response->getTemplate(), $response->getTemplateVariables())
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -90], // Before ProfilerListener
        ];
    }
}
