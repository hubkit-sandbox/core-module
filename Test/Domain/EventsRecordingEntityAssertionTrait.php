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

namespace ParkManager\Module\CoreModule\Test\Domain;

use ParkManager\Module\CoreModule\Domain\EventsRecordingEntity;

trait EventsRecordingEntityAssertionTrait
{
    /**
     * @param EventsRecordingEntity                            $entity
     * @param \ParkManager\Component\DomainEvent\DomainEvent[] $expectedEvents
     */
    protected static function assertDomainEvents(EventsRecordingEntity $entity, array $expectedEvents): void
    {
        $events = $entity->releaseEvents();

        foreach ($expectedEvents as $i => $event) {
            self::assertArrayHasKey($i, $events, 'Event must exist at position.');
            self::assertEquals(\get_class($events[$i]), \get_class($event), 'Event at position must be of same type');
        }

        self::assertCount($c = \count($expectedEvents), $events, sprintf('Expected exactly "%d" events.', $c));
    }

    protected static function assertNoDomainEvents(EventsRecordingEntity $entity): void
    {
        $events = $entity->releaseEvents();

        self::assertCount(0, $events, sprintf('Expected exactly no events.'));
    }

    protected static function resetDomainEvents(EventsRecordingEntity ...$entities): void
    {
        foreach ($entities as $entity) {
            $entity->releaseEvents();
        }
    }
}
