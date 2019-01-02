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

use Doctrine\ORM\EntityManagerInterface;
use ParkManager\Module\CoreModule\Domain\Administrator\Administrator;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorId;
use ParkManager\Module\CoreModule\Domain\Administrator\AdministratorRepository;
use ParkManager\Module\CoreModule\Domain\Administrator\Exception\AdministratorNotFound;
use ParkManager\Module\CoreModule\Domain\Client\Exception\EmailChangeConfirmationRejected;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\EventSourcedEntityRepository;
use Symfony\Component\Messenger\MessageBusInterface as MessageBus;

/**
 * @method Administrator find($id, $lockMode = null, $lockVersion = null)
 */
final class DoctrineOrmAdministratorRepository extends EventSourcedEntityRepository implements AdministratorRepository
{
    public function __construct(EntityManagerInterface $entityManager, MessageBus $eventBus, string $className = Administrator::class)
    {
        parent::__construct($entityManager, $eventBus, $className);
    }

    public function get(AdministratorId $id): Administrator
    {
        $administrator = $this->find($id);

        if ($administrator === null) {
            throw AdministratorNotFound::withId($id);
        }

        return $administrator;
    }

    public function save(Administrator $administrator): void
    {
        $this->_em->persist($administrator);

        $this->doDispatchEvents($administrator);
    }

    public function remove(Administrator $administrator): void
    {
        $this->_em->remove($administrator);
    }

    public function getByEmail(EmailAddress $email): Administrator
    {
        $client = $this->createQueryBuilder('u')
            ->where('u.email.canonical = :email')
            ->getQuery()
            ->setParameter('email', $email->canonical())
            ->getOneOrNullResult();

        if ($client === null) {
            throw AdministratorNotFound::withEmail($email);
        }

        return $client;
    }

    public function getByEmailAddressChangeToken(string $selector): Administrator
    {
        $client = $this->createQueryBuilder('u')
            ->where('u.emailAddressChangeToken.selector = :selector')
            ->getQuery()
            ->setParameter('selector', $selector)
            ->getOneOrNullResult();

        if ($client === null) {
            throw new EmailChangeConfirmationRejected();
        }

        return $client;
    }

    public function getByPasswordResetToken(string $selector): Administrator
    {
        $client = $this->createQueryBuilder('u')
            ->where('u.passwordResetToken.selector = :selector')
            ->getQuery()
            ->setParameter('selector', $selector)
            ->getOneOrNullResult();

        if ($client === null) {
            throw new PasswordResetTokenNotAccepted();
        }

        return $client;
    }
}
