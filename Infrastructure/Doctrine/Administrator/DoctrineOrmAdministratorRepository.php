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

namespace ParkManager\Module\CoreModule\Infrastructure\Doctrine\Administrator;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use ParkManager\Component\DomainEvent\EventEmitter;
use ParkManager\Module\CoreModule\Domain\Administrator\Administrator;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorRepository;
use ParkManager\Module\CoreModule\Domain\Administrator\Exception\AdministratorNotFound;
use ParkManager\Module\CoreModule\Domain\Shared\AbstractUser;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\EntityRepository;

/**
 * @method Administrator find($id, $lockMode = null, $lockVersion = null)
 */
final class DoctrineOrmAdministratorRepository extends EntityRepository implements AdministratorRepository
{
    private $eventEmitter;

    public function __construct(EntityManagerInterface $entityManager, EventEmitter $eventEmitter, string $className = Administrator::class)
    {
        parent::__construct($entityManager, $className);
        $this->eventEmitter = $eventEmitter;
    }

    public function get($id): Administrator
    {
        Assertion::isInstanceOf($id, AdministratorId::class);

        $administrator = $this->find($id);

        if ($administrator === null) {
            throw AdministratorNotFound::withId($id);
        }

        return $administrator;
    }

    public function save(AbstractUser $administrator): void
    {
        Assertion::isInstanceOf($administrator, Administrator::class);

        $this->_em->persist($administrator);

        foreach ($administrator->releaseEvents() as $event) {
            $this->eventEmitter->emit($event);
        }
    }

    public function remove(Administrator $administrator): void
    {
        $this->_em->remove($administrator);
    }

    public function findByEmailAddress(EmailAddress $email): ?Administrator
    {
        return $this->createQueryBuilder('u')
            ->where('u.email.canonical = :email')
            ->getQuery()
            ->setParameter('email', $email->canonical())
            ->getOneOrNullResult()
        ;
    }

    public function getByPasswordResetToken(string $selector): Administrator
    {
        $administrator = $this->createQueryBuilder('u')
            ->where('u.passwordResetToken.selector = :selector')
            ->getQuery()
            ->setParameter('selector', $selector)
            ->getOneOrNullResult();

        if ($administrator === null) {
            throw new PasswordResetTokenNotAccepted();
        }

        return $administrator;
    }
}
