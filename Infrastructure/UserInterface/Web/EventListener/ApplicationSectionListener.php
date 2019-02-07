<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\EventListener;

use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\ApplicationContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApplicationSectionListener implements EventSubscriberInterface
{
    /** @var RequestMatcherInterface[] */
    private $sectionMatchers;

    /** @var ApplicationContext */
    private $applicationContext;

    /**
     * @param RequestMatcherInterface[] $sectionMatchers
     */
    public function __construct(array $sectionMatchers, ApplicationContext $applicationContext)
    {
        $this->sectionMatchers    = $sectionMatchers;
        $this->applicationContext = $applicationContext;
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

                break;
            }
        }
    }

    public function reset(): void
    {
        $this->applicationContext->reset();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -8], // Before the Firewall
        ];
    }
}
