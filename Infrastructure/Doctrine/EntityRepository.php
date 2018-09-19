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
use Doctrine\ORM\EntityRepository as BaseEntityRepository;

abstract class EntityRepository extends BaseEntityRepository
{
    public function __construct(EntityManagerInterface $entityManager, string $className)
    {
        $this->_em         = $entityManager;
        $this->_class      = $entityManager->getMetadataFactory()->getMetadataFor($className);
        $this->_entityName = $className;
    }
}
