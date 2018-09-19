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

namespace ParkManager\Module\CoreModule\Domain;

use ParkManager\Component\DomainEvent\DomainEvent;

/**
 * The DomainEventsCollectionTrait keeps track of recorded events.
 */
trait DomainEventsCollectionTrait
{
    protected $domainEvents = [];

    protected function recordThat(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * @return DomainEvent[]
     */
    public function releaseEvents(): array
    {
        $pendingEvents      = $this->domainEvents;
        $this->domainEvents = [];

        return $pendingEvents;
    }
}
