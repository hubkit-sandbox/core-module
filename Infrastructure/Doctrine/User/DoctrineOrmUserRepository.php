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

namespace ParkManager\Module\CoreModule\Infrastructure\Doctrine\User;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use ParkManager\Module\CoreModule\Domain\Shared\AbstractUser;
use ParkManager\Module\CoreModule\Domain\Shared\AbstractUserId;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use ParkManager\Module\CoreModule\Domain\User\Exception\UserNotFound;
use ParkManager\Module\CoreModule\Domain\User\User;
use ParkManager\Module\CoreModule\Domain\User\UserId;
use ParkManager\Module\CoreModule\Domain\User\UserRepository;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\EntityRepository;
use Symfony\Component\Messenger\MessageBusInterface as MessageBus;

/**
 * @method User find($id, $lockMode = null, $lockVersion = null)
 * @method User findOneBy(array $criteria, array $orderBy = null)
 */
class DoctrineOrmUserRepository extends EntityRepository implements UserRepository
{
    protected $eventBus;

    public function __construct(EntityManagerInterface $entityManager, MessageBus $eventBus, string $className = User::class)
    {
        parent::__construct($entityManager, $className);
        $this->eventBus = $eventBus;
    }

    /**
     * @param UserId $id
     */
    public function get(AbstractUserId $id): User
    {
        Assertion::isInstanceOf($id, UserId::class);

        $user = $this->find($id);

        if ($user === null) {
            throw UserNotFound::withUserId($id);
        }

        return $user;
    }

    public function save(AbstractUser $user): void
    {
        Assertion::isInstanceOf($user, User::class);
        $this->_em->persist($user);

        foreach ($user->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }

    public function remove(User $user): void
    {
        $this->_em->remove($user);
    }

    public function findByEmailAddress(EmailAddress $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.email.canonical = :email')
            ->getQuery()
            ->setParameter('email', $email->canonical())
            ->getOneOrNullResult()
        ;
    }

    public function getByEmailAddressChangeToken(string $selector): User
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.emailAddressChangeToken.selector = :selector')
            ->getQuery()
            ->setParameter('selector', $selector)
            ->getOneOrNullResult();

        if ($user === null) {
            throw new PasswordResetTokenNotAccepted();
        }

        return $user;
    }

    public function getByPasswordResetToken(string $selector): User
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.passwordResetToken.selector = :selector')
            ->getQuery()
            ->setParameter('selector', $selector)
            ->getOneOrNullResult();

        if ($user === null) {
            throw new PasswordResetTokenNotAccepted();
        }

        return $user;
    }
}
