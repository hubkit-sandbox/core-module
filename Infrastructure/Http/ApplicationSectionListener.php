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

namespace ParkManager\Module\CoreModule\Infrastructure\Http;

use ParkManager\Module\CoreModule\Infrastructure\Context\ApplicationContext;
use ParkManager\Module\CoreModule\Infrastructure\Context\SwitchableUserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApplicationSectionListener implements EventSubscriberInterface
{
    private $sectionMatchers;
    private $applicationContext;
    private $userRepository;

    /**
     * @param RequestMatcherInterface[] $sectionMatchers
     */
    public function __construct(array $sectionMatchers, ApplicationContext $applicationContext, SwitchableUserRepository $userRepository)
    {
        $this->sectionMatchers    = $sectionMatchers;
        $this->applicationContext = $applicationContext;
        $this->userRepository     = $userRepository;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (! $event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        foreach ($this->sectionMatchers as $name => $matcher) {
            if ($matcher->matches($request)) {
                $request->attributes->set('_app_section', $name);

                $this->applicationContext->setActiveSection($name);
                $this->userRepository->setActive($name);

                break;
            }
        }
    }

    public function reset()
    {
        $this->applicationContext->reset();
        $this->userRepository->reset();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -8], // Before the Firewall
        ];
    }
}
