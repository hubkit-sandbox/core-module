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

namespace ParkManager\Module\CoreModule\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ParkManager\Component\DomainEvent\EventEmitter;
use ParkManager\Module\CoreModule\Domain\EventsRecordingEntity;

abstract class EventSourcedEntityRepository extends EntityRepository
{
    protected $eventBus;

    public function __construct(EntityManagerInterface $entityManager, EventEmitter $eventBus, string $className)
    {
        $this->_em         = $entityManager;
        $this->_class      = $entityManager->getClassMetadata($className);
        $this->_entityName = $className;
        $this->eventBus    = $eventBus;
    }

    protected function doDispatchEvents(EventsRecordingEntity $aggregateRoot): void
    {
        foreach ($aggregateRoot->releaseEvents() as $event) {
            $this->eventBus->emit($event);
        }
    }
}
