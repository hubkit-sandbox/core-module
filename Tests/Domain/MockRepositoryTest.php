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

namespace ParkManager\Module\CoreModule\Tests\Domain;

use InvalidArgumentException;
use ParkManager\Module\CoreModule\Test\Domain\MockRepository;
use ParkManager\Module\CoreModule\Tests\Domain\Mock\EmailChanged;
use ParkManager\Module\CoreModule\Tests\Domain\Mock\MockEntity;
use ParkManager\Module\CoreModule\Tests\Domain\Mock\MockIdentity;
use PHPUnit\Framework\TestCase;
use function mb_strtolower;

/**
 * @internal
 */
final class MockRepositoryTest extends TestCase
{
    /** @test */
    public function it_has_no_enties_saved_or_removed()
    {
        $repository = new class() {
            use MockRepository;

            protected function throwOnNotFound($key): void
            {
                throw new InvalidArgumentException('No, I has not have that key: ' . $key);
            }
        };

        $repository->assertNoEntitiesWereSaved();
        $repository->assertNoEntitiesWereRemoved();
    }

    /** @test */
    public function it_gets_entity()
    {
        $entity1 = new MockEntity('fc86687e-0875-11e9-9701-acbc32b58315');
        $entity2 = new MockEntity('9dab0b6a-0876-11e9-bfd1-acbc32b58315');

        $repository = new class([$entity1, $entity2]) {
            use MockRepository;

            protected function throwOnNotFound($key): void
            {
                throw new InvalidArgumentException('No, I has not have that key: ' . $key);
            }

            public function get(MockIdentity $id): MockEntity
            {
                return $this->mockDoGetById($id);
            }
        };

        $repository->assertNoEntitiesWereSaved();
        $repository->assertNoEntitiesWereRemoved();
        $repository->assertHasEntity($entity1->id(), static function () { });
        $repository->assertHasEntity($entity2->id(), static function () { });
        self::assertSame($entity1, $repository->get($entity1->id()));
        self::assertSame($entity2, $repository->get($entity2->id()));
    }

    /** @test */
    public function it_gets_entity_by_field_method()
    {
        $entity1 = new MockEntity('fc86687e-0875-11e9-9701-acbc32b58315', 'John');
        $entity2 = new MockEntity('9dab0b6a-0876-11e9-bfd1-acbc32b58315', 'Jane');

        $repository = new class([$entity1, $entity2]) {
            use MockRepository;

            protected function throwOnNotFound($key): void
            {
                throw new InvalidArgumentException('No, I has not have that key: ' . $key);
            }

            protected function getFieldsIndexMapping(): array
            {
                return ['last_name' => 'lastName'];
            }

            public function getByLastName(string $name): MockEntity
            {
                return $this->mockDoGetByField('last_name', $name);
            }
        };

        self::assertSame($entity1, $repository->getByLastName('John'));
        self::assertSame($entity2, $repository->getByLastName('Jane'));
    }

    /** @test */
    public function it_gets_entity_by_field_property()
    {
        $entity1       = new MockEntity('fc86687e-0875-11e9-9701-acbc32b58315');
        $entity1->name = 'John';

        $entity2       = new MockEntity('9dab0b6a-0876-11e9-bfd1-acbc32b58315');
        $entity2->name = 'Jane';

        $repository = new class([$entity1, $entity2]) {
            use MockRepository;

            protected function throwOnNotFound($key): void
            {
                throw new InvalidArgumentException('No, I has not have that key: ' . $key);
            }

            protected function getFieldsIndexMapping(): array
            {
                return ['Name' => '#name'];
            }

            public function getByName(string $name): MockEntity
            {
                return $this->mockDoGetByField('Name', $name);
            }
        };

        self::assertSame($entity1, $repository->getByName('John'));
        self::assertSame($entity2, $repository->getByName('Jane'));
    }

    /** @test */
    public function it_gets_entity_by_field_closure()
    {
        $entity1 = new MockEntity('fc86687e-0875-11e9-9701-acbc32b58315', 'John');
        $entity2 = new MockEntity('9dab0b6a-0876-11e9-bfd1-acbc32b58315', 'Jane');

        $repository = new class([$entity1, $entity2]) {
            use MockRepository;

            protected function throwOnNotFound($key): void
            {
                throw new InvalidArgumentException('No, I has not have that key: ' . $key);
            }

            protected function getFieldsIndexMapping(): array
            {
                return ['last_name' => static function (MockEntity $entity) { return mb_strtolower($entity->lastName()); }];
            }

            public function getByLastName(string $name): MockEntity
            {
                return $this->mockDoGetByField('last_name', $name);
            }
        };

        self::assertSame($entity1, $repository->getByLastName('john'));
        self::assertSame($entity2, $repository->getByLastName('jane'));
    }

    /** @test */
    public function it_gets_entity_by_event()
    {
        $entity1 = new MockEntity('fc86687e-0875-11e9-9701-acbc32b58315', 'John');
        $entity1->changeEmail('John@example.com');

        $entity2 = new MockEntity('9dab0b6a-0876-11e9-bfd1-acbc32b58315', 'Jane');
        $entity2->changeEmail('Jane@example.com');

        $repository = new class([$entity1, $entity2]) {
            use MockRepository;

            protected function throwOnNotFound($key): void
            {
                throw new InvalidArgumentException('No, I has not have that key: ' . $key);
            }

            protected function getEventsIndexMapping(): array
            {
                return [EmailChanged::class => 'email'];
            }

            public function getByEmail(string $email): MockEntity
            {
                return $this->mockDoGetByEvent(EmailChanged::class, $email);
            }
        };

        self::assertSame($entity1, $repository->getByEmail('John@example.com'));
        self::assertSame($entity2, $repository->getByEmail('Jane@example.com'));
    }

    /** @test */
    public function it_gets_entity_by_event_with_multiple_fired()
    {
        $entity1 = new MockEntity('fc86687e-0875-11e9-9701-acbc32b58315', 'John');
        $entity1->changeEmail('John@example.com');
        $entity1->changeEmail('John2@example.com');

        $entity2 = new MockEntity('9dab0b6a-0876-11e9-bfd1-acbc32b58315', 'Jane');
        $entity2->changeEmail('Jane@example.com');

        $repository = new class([$entity1, $entity2]) {
            use MockRepository;

            protected function throwOnNotFound($key): void
            {
                throw new InvalidArgumentException('No, I has not have that key: ' . $key);
            }

            protected function getEventsIndexMapping(): array
            {
                return [EmailChanged::class => 'email'];
            }

            public function getByEmail(string $email): MockEntity
            {
                return $this->mockDoGetByEvent(EmailChanged::class, $email);
            }
        };

        self::assertSame($entity1, $repository->getByEmail('John2@example.com'));
        self::assertSame($entity2, $repository->getByEmail('Jane@example.com'));
    }

    /** @test */
    public function it_saves_entity()
    {
        $entity1       = new MockEntity('fc86687e-0875-11e9-9701-acbc32b58315');
        $entity1->name = 'John';

        $entity2       = new MockEntity('9dab0b6a-0876-11e9-bfd1-acbc32b58315');
        $entity2->name = 'Jane';

        $repository = new class([$entity1, $entity2]) {
            use MockRepository;

            protected function throwOnNotFound($key): void
            {
                throw new InvalidArgumentException('No, I has not have that key: ' . $key);
            }

            protected function getFieldsIndexMapping(): array
            {
                return ['Name' => '#name'];
            }

            public function getByName(string $name): MockEntity
            {
                return $this->mockDoGetByField('Name', $name);
            }

            public function save(MockEntity $entity)
            {
                $this->mockDoSave($entity);
            }
        };

        $entity1->name = 'Jones';

        $repository->save($entity1);

        $repository->assertEntitiesWereSaved();
        $repository->assertNoEntitiesWereRemoved();
        self::assertSame($entity1, $repository->getByName('Jones'));
        self::assertSame($entity2, $repository->getByName('Jane'));
    }

    /** @test */
    public function it_removes_entity()
    {
        $entity1 = new MockEntity('fc86687e-0875-11e9-9701-acbc32b58315');
        $entity2 = new MockEntity('9dab0b6a-0876-11e9-bfd1-acbc32b58315');

        $repository = new class([$entity1, $entity2]) {
            use MockRepository;

            protected function throwOnNotFound($key): void
            {
                throw new InvalidArgumentException('No, I has not have that key: ' . $key);
            }

            public function get(MockIdentity $id): MockEntity
            {
                return $this->mockDoGetById($id);
            }

            public function remove(MockEntity $entity)
            {
                $this->mockDoRemove($entity);
            }
        };

        $repository->remove($entity1);
        $repository->assertNoEntitiesWereSaved();

        $repository->assertEntitiesWereRemoved([$entity1]);
        $repository->assertHasEntity($entity2->id(), static function () { });
        self::assertSame($entity2, $repository->get($entity2->id()));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No, I has not have that key: ' . $entity1->id());

        $repository->get($entity1->id());
    }
}
