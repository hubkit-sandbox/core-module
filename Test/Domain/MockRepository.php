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

use Closure;
use ParkManager\Module\CoreModule\Domain\RecordsDomainEvents;
use PHPUnit\Framework\Assert;
use Throwable;
use function array_values;
use function get_class;
use function mb_strpos;
use function mb_substr;
use function method_exists;
use function sprintf;
use function ucfirst;

/**
 * Helps to quickly set-up an in-memory repository.
 */
trait MockRepository
{
    use EventsRecordingEntityAssertionTrait {
        resetDomainEvents as protected __resetDomainEvents;
    }

    /** @var object[]|RecordsDomainEvents[] */
    protected $storedById = [];

    /** @var object[]|RecordsDomainEvents[] */
    protected $savedById = [];

    /** @var object[]|RecordsDomainEvents[] */
    protected $removedById = [];

    /**
     * Counter of saved entities (in total).
     *
     * @var int
     */
    protected $mockWasSaved = 0;

    /**
     * Count of removed entities (in total).
     *
     * @var int
     */
    protected $mockWasRemoved = 0;

    /** @var array<string,string<object>> [mapping-name][index-key] => {entity} */
    protected $storedByField = [];

    /** @var array<string,string<object>> [mapping-name][index-key] => {entity} */
    protected $storedByEvents = [];

    /**
     * @param object[] $initialEntities Array of initial entities (these are not counted as saved)
     */
    public function __construct(array $initialEntities = [])
    {
        foreach ($initialEntities as $entity) {
            $this->setInMockedStorage($entity);

            if ($entity instanceof RecordsDomainEvents) {
                $entity->releaseEvents();
            }
        }
    }

    private function setInMockedStorage(object $entity): void
    {
        $this->storedById[$this->getValueWithGetter($entity, 'id')->toString()] = $entity;

        foreach ($this->getFieldsIndexMapping() as $mapping => $getter) {
            $this->storedByField[$mapping][$this->getValueWithGetter($entity, $getter)] = $entity;
        }

        if ($entity instanceof RecordsDomainEvents && $eventMapping = $this->getEventsIndexMapping()) {
            $eventsByClass = [];

            foreach ($entity->getEvents() as $event) {
                $eventsByClass[get_class($event)] = $event;
            }

            foreach ($eventMapping as $eventName => $getter) {
                if (! isset($eventsByClass[$eventName])) {
                    continue;
                }

                $this->storedByEvents[$eventName][$this->getValueWithGetter($eventsByClass[$eventName], $getter)] = $entity;
            }
        }
    }

    /**
     * @param string|Closure $getter
     *
     * @return mixed
     */
    private function getValueWithGetter(object $object, $getter)
    {
        if ($getter instanceof Closure) {
            return $getter($object);
        }

        if (mb_strpos($getter, '#') === 0) {
            return $object->{mb_substr($getter, 1)};
        }

        return $object->{(method_exists($object, $getter) ? $getter : 'get' . ucfirst($getter))}();
    }

    /**
     * Returns a list fields (#property, method-name or Closure for extracting)
     * to use for mapping the entity in storage.
     *
     * @return array [mapping-name] => '#property or method'
     */
    protected function getFieldsIndexMapping(): array
    {
        return [];
    }

    /**
     * Returns a list events (#property, method-name or Closure for extracting)
     * to use for mapping the entity in storage.
     *
     * @return array [event-name] => '#property or method'
     */
    protected function getEventsIndexMapping(): array
    {
        return [];
    }

    protected function mockDoSave(object $entity): void
    {
        $this->setInMockedStorage($entity);
        $this->savedById[$this->getValueWithGetter($entity, 'id')->toString()] = $entity;
        ++$this->mockWasSaved;
    }

    protected function mockDoRemove(object $entity): void
    {
        $this->removedById[$this->getValueWithGetter($entity, 'id')->toString()] = $entity;
        ++$this->mockWasRemoved;
    }

    /**
     * @return mixed
     */
    protected function mockDoGetById(object $id)
    {
        if (! isset($this->storedById[$id->toString()])) {
            $this->throwOnNotFound($id);
        }

        $this->guardNotRemoved($id);

        return $this->storedById[$id->toString()];
    }

    protected function guardNotRemoved(object $id): void
    {
        if (isset($this->removedById[$id->toString()])) {
            $this->throwOnNotFound($id);
        }
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function mockDoGetByField(string $key, $value)
    {
        if (! isset($this->storedByField[$key][$value])) {
            $this->throwOnNotFound($value);
        }

        $entity = $this->storedByField[$key][$value];
        $this->guardNotRemoved($this->getValueWithGetter($entity, 'id'));

        return $entity;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function mockDoGetByEvent(string $event, $value)
    {
        if (! isset($this->storedByEvents[$event][$value])) {
            $this->throwOnNotFound($value);
        }

        $entity = $this->storedByEvents[$event][$value];
        $this->guardNotRemoved($this->getValueWithGetter($entity, 'id'));

        return $entity;
    }

    /**
     * @param mixed $key
     *
     * @throws Throwable
     */
    abstract protected function throwOnNotFound($key): void;

    public function assertNoEntitiesWereSaved(): void
    {
        Assert::assertEquals(0, $this->mockWasSaved, 'No entities were expected to be stored');
    }

    public function assertEntitiesWereSaved(array $entities = []): void
    {
        Assert::assertGreaterThan(0, $this->mockWasSaved, 'Entities were expected to be stored');

        if ($entities) {
            Assert::assertEquals($entities, array_values($this->savedById));
        }
    }

    public function assertNoEntitiesWereRemoved(): void
    {
        if ($this->mockWasRemoved > 0) {
            Assert::fail(sprintf('No entities were expected to be removed, but %d entities were removed.', $this->mockWasSaved));
        }
    }

    public function assertEntitiesWereRemoved(array $entities): void
    {
        Assert::assertGreaterThan(0, $this->mockWasRemoved, 'No entities were removed');
        Assert::assertEquals($entities, array_values($this->removedById));
    }

    public function assertHasEntity($id, Closure $excepted): void
    {
        $key = (string) $id;
        Assert::assertArrayHasKey($key, $this->storedById);
        $excepted($this->storedById[$key]);
    }

    /**
     * @param string|object $id
     * @param object[]      $exceptedEvents
     */
    public function assertHasEntityWithEvents($id, array $exceptedEvents, ?callable $assertionValidator = null): void
    {
        $key = (string) $id;

        Assert::arrayHasKey($key);

        /** @var RecordsDomainEvents $entity */
        $entity = $this->storedById[$key];

        self::assertDomainEvents($entity, $exceptedEvents, $assertionValidator);
    }
}
