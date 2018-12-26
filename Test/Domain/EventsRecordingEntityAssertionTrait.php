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

use ParkManager\Module\CoreModule\Domain\RecordsDomainEvents;
use PHPUnit\Framework\Assert;
use function array_map;
use function count;
use function get_class;
use function implode;
use function sprintf;

trait EventsRecordingEntityAssertionTrait
{
    protected static function assertDomainEvents(RecordsDomainEvents $entity, iterable $expectedEvents, ?callable $comparator = null): void
    {
        $events = $entity->releaseEvents();

        foreach ($expectedEvents as $i => $event) {
            Assert::assertArrayHasKey($i, $events, 'Event must exist at position.');
            Assert::assertEquals(get_class($events[$i]), get_class($event), 'Event at position must be of same type');

            if ($comparator !== null) {
                $comparator($event, $events[$i]);
            } else {
                Assert::assertEquals($event, $events[$i]);
                DomainMessageAssertion::assertMessagesAreEqual($event, $events[$i]);
            }
        }

        Assert::assertCount(
            $c = count($expectedEvents),
            $events,
            sprintf('Expected exactly %d events, but %d were recorded (%s).', $c, count($events), implode(', ', array_map('get_class', $events)))
        );
    }

    protected static function assertNoDomainEvents(RecordsDomainEvents $entity): void
    {
        $events = $entity->releaseEvents();
        Assert::assertCount(0, $events, sprintf('Expected exactly no events.'));
    }

    protected static function resetDomainEvents(RecordsDomainEvents ...$entities): void
    {
        foreach ($entities as $entity) {
            $entity->releaseEvents();
        }
    }
}
