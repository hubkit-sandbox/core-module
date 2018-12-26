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

namespace ParkManager\Module\CoreModule\Test\Domain\Repository;

use ParkManager\Module\CoreModule\Domain\Client\Client;
use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Domain\Client\ClientRepository;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientEmailAddressChangeWasRequested;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientPasswordResetWasRequested;
use ParkManager\Module\CoreModule\Domain\Client\Exception\ClientNotFound;
use ParkManager\Module\CoreModule\Domain\Client\Exception\EmailChangeConfirmationRejected;
use ParkManager\Module\CoreModule\Domain\Client\Exception\PasswordResetConfirmationRejected;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Test\Domain\MockRepository;

final class ClientRepositoryMock implements ClientRepository
{
    public const USER_ID1 = '01dd5964-5426-11e7-be03-acbc32b58315';

    use MockRepository;

    protected function getFieldsIndexMapping(): array
    {
        return [
            'email' => function (Client $client) {
                return $client->email()->canonical();
            },
        ];
    }

    protected function getEventsIndexMapping(): array
    {
        return [
            ClientPasswordResetWasRequested::class => function (ClientPasswordResetWasRequested $e) {
                return $e->token()->selector();
            },
            ClientEmailAddressChangeWasRequested::class => function (ClientEmailAddressChangeWasRequested $e) {
                return $e->token()->selector();
            },
        ];
    }

    public static function createClient($email = 'janE@example.com', $id = self::USER_ID1): Client
    {
        return Client::register(ClientId::fromString($id), new EmailAddress($email), 'J', 'nope');
    }

    public function get(ClientId $id): Client
    {
        return $this->mockDoGetById($id);
    }

    public function getByEmail(EmailAddress $email): Client
    {
        return $this->mockDoGetByField('email', $email->canonical());
    }

    public function getByPasswordResetToken(string $selector): Client
    {
        try {
            return $this->mockDoGetByEvent(ClientPasswordResetWasRequested::class, $selector);
        } catch (ClientNotFound $e) {
            throw new PasswordResetConfirmationRejected();
        }
    }

    public function getByEmailAddressChangeToken(string $selector): Client
    {
        try {
            return $this->mockDoGetByEvent(ClientEmailAddressChangeWasRequested::class, $selector);
        } catch (ClientNotFound $e) {
            throw new EmailChangeConfirmationRejected();
        }
    }

    public function save(Client $administrator): void
    {
        $this->mockDoSave($administrator);
    }

    public function remove(Client $administrator): void
    {
        $this->mockDoRemove($administrator);
    }

    protected function throwOnNotFound($key): void
    {
        throw new ClientNotFound((string) $key);
    }
}
