<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\Doctrine\Client;

use Doctrine\ORM\EntityManagerInterface;
use ParkManager\Module\CoreModule\Domain\Client\Client;
use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Domain\Client\ClientRepository;
use ParkManager\Module\CoreModule\Domain\Client\Exception\ClientNotFound;
use ParkManager\Module\CoreModule\Domain\Client\Exception\EmailChangeConfirmationRejected;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\EntityRepository;
use Symfony\Component\Messenger\MessageBusInterface as MessageBus;

/**
 * @method Client find($id, $lockMode = null, $lockVersion = null)
 * @method Client findOneBy(array $criteria, array $orderBy = null)
 */
class DoctrineOrmClientRepository extends EntityRepository implements ClientRepository
{
    protected $eventBus;

    public function __construct(EntityManagerInterface $entityManager, MessageBus $eventBus, string $className = Client::class)
    {
        parent::__construct($entityManager, $className);
        $this->eventBus = $eventBus;
    }

    public function get(ClientId $id): Client
    {
        $user = $this->find($id);

        if ($user === null) {
            throw ClientNotFound::withId($id);
        }

        return $user;
    }

    public function save(Client $user): void
    {
        $this->_em->persist($user);

        foreach ($user->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }

    public function remove(Client $user): void
    {
        $this->_em->remove($user);
    }

    public function getByEmail(EmailAddress $email): Client
    {
        $client = $this->createQueryBuilder('u')
            ->where('u.email.canonical = :email')
            ->getQuery()
            ->setParameter('email', $email->canonical())
            ->getOneOrNullResult();

        if ($client === null) {
            throw ClientNotFound::withEmail($email);
        }

        return $client;
    }

    public function getByEmailAddressChangeToken(string $selector): Client
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

    public function getByPasswordResetToken(string $selector): Client
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
