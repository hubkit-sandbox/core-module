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

namespace ParkManager\Module\CoreModule\Test\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class EntityRepositoryTestCase extends KernelTestCase
{
    protected function setUp()
    {
        parent::setUp();
        self::bootKernel();

        $this->setUpDatabaseTransaction();
    }

    protected function tearDown()
    {
        $this->tearDownDatabaseTransaction();
        parent::tearDown();
    }

    protected function assertInTransaction(?string $manager = null)
    {
        self::assertTrue($this->getEntityManager($manager)->getConnection()->getTransactionNestingLevel() > 0, 'Expected to be in a transactional');
    }

    protected function setUpDatabaseTransaction(?string $manager = null): void
    {
        $em = $this->getEntityManager($manager);
        while ($em->getConnection()->getTransactionNestingLevel() > 0) {
            $em->rollback();
        }

        $em->beginTransaction();
    }

    protected function tearDownDatabaseTransaction(?string $manager = null): void
    {
        $em = $this->getEntityManager($manager);
        while ($em->getConnection()->getTransactionNestingLevel() > 0) {
            $em->rollback();
        }
    }

    protected function getEntityManager(?string $manager = 'doctrine.orm.default_entity_manager'): EntityManagerInterface
    {
        /** @var EntityManagerInterface $manager */
        $manager = self::$container->get($manager ?? $this->getDefaultManagerName());

        return $manager;
    }

    protected function getDefaultManagerName(): string
    {
        return 'doctrine.orm.default_entity_manager';
    }
}
