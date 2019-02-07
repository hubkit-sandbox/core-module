<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Domain;

/**
 * The DomainEventsCollectionTrait keeps track of recorded events.
 */
trait DomainEventsCollectionTrait
{
    protected $domainEvents = [];

    protected function recordThat(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * @return object[]
     */
    public function releaseEvents(): array
    {
        $pendingEvents      = $this->domainEvents;
        $this->domainEvents = [];

        return $pendingEvents;
    }

    /**
     * @return object[]
     */
    public function getEvents(): array
    {
        return $this->domainEvents;
    }
}
