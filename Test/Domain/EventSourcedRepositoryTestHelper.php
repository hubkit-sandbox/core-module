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

use ParkManager\Component\DomainEvent\EventEmitter;
use Prophecy\Argument;

trait EventSourcedRepositoryTestHelper
{
    protected function createEventsExpectingEventBus(int $expectedEventsCount = -1): EventEmitter
    {
        $eventBusProphecy = $this->prophesize(EventEmitter::class);

        if ($expectedEventsCount === -1) {
            $eventBusProphecy->emit(Argument::any())->shouldBeCalled();
        } else {
            $eventBusProphecy->emit(Argument::any())->shouldBeCalledTimes($expectedEventsCount);
        }

        return $eventBusProphecy->reveal();
    }

    protected function createNoExpectedEventsDispatchedEventBus(): EventEmitter
    {
        $eventBusProphecy = $this->prophesize(EventEmitter::class);
        $eventBusProphecy->emit(Argument::any())->shouldNotBeCalled();

        return $eventBusProphecy->reveal();
    }
}
